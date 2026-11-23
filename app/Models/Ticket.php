<?php

namespace App\Models;

use App\Enums\TicketCategory;
use App\Enums\TicketLocale;
use App\Enums\TicketPriority;
use App\Enums\TicketSource;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Jobs\SyncTicketChangeToPmsJob;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

/**
 * @property int $id
 * @property string $ticket_number
 * @property TicketStatus $status
 * @property TicketPriority $priority
 * @property TicketLocale $locale
 * @property array<int, string>|null $attachments
 * @property int|null $assigned_to_id
 */
#[Fillable([
    'assigned_to_id',
    'reporter_name',
    'reporter_email',
    'locale',
    'source',
    'source_app_id',
    'tracking_token_hash',
    'title',
    'description',
    'page_url',
    'device_metadata',
    'attachments',
    'status',
    'priority',
    'category',
    'ai_priority',
    'ai_confidence',
    'ai_summary',
    'ai_model',
    'ai_classified_at',
])]
class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket): void {
            $ticket->ticket_number ??= self::generateTicketNumber();
        });

        static::updating(function (Ticket $ticket): void {
            if (! $ticket->isDirty('status')) {
                return;
            }

            $from = $ticket->getOriginal('status');
            $to = $ticket->status;

            if (! $from instanceof TicketStatus || ! $from->canTransitionTo($to)) {
                throw ValidationException::withMessages([
                    'data.status' => __('filament/resources/tickets.messages.invalid_transition'),
                ]);
            }

            if ($to === TicketStatus::Closed && $from !== TicketStatus::Resolved && ! self::actorCanCloseOpenTickets()) {
                throw ValidationException::withMessages([
                    'data.status' => __('filament/resources/tickets.messages.team_lead_required_to_close'),
                ]);
            }
        });

        static::created(function (Ticket $ticket): void {
            TicketActivity::record($ticket, 'ticket_created');
        });

        static::updated(function (Ticket $ticket): void {
            $tracked = [
                'status' => 'status_changed',
                'priority' => 'priority_changed',
                'assigned_to_id' => 'assignee_changed',
            ];

            foreach ($tracked as $attribute => $event) {
                if ($ticket->wasChanged($attribute)) {
                    TicketActivity::record($ticket, $event, [
                        'from' => $ticket->getOriginal($attribute),
                        'to' => $ticket->getAttribute($attribute),
                    ]);
                }
            }

            $staffChangedStatusOrPriority = auth()->check()
                && (($ticket->wasChanged('status') && $ticket->status !== TicketStatus::PushedToPms)
                    || $ticket->wasChanged('priority'));

            if ($staffChangedStatusOrPriority && $ticket->projectSyncs()->exists()) {
                SyncTicketChangeToPmsJob::dispatch($ticket->getKey());
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'priority' => TicketPriority::class,
            'category' => TicketCategory::class,
            'ai_priority' => TicketPriority::class,
            'ai_confidence' => 'decimal:3',
            'ai_classified_at' => 'datetime',
            'locale' => TicketLocale::class,
            'source' => TicketSource::class,
            'device_metadata' => 'array',
            'attachments' => 'array',
        ];
    }

    /**
     * @return HasMany<ProjectSync, $this>
     */
    public function projectSyncs(): HasMany
    {
        return $this->hasMany(ProjectSync::class);
    }

    /**
     * @return BelongsTo<SourceApp, $this>
     */
    public function sourceApp(): BelongsTo
    {
        return $this->belongsTo(SourceApp::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    /**
     * @return HasMany<TicketActivity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(TicketActivity::class)->latest('created_at');
    }

    /**
     * Super Admins see everything. Team Leads see their own tickets, their direct reports' tickets,
     * and unassigned tickets. Support Agents see their own tickets and unassigned tickets.
     *
     * @param  Builder<Ticket>  $query
     * @return Builder<Ticket>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isRole(UserRole::SuperAdmin)) {
            return $query;
        }

        $visibleAssigneeIds = [$user->id];

        if ($user->isRole(UserRole::TeamLead)) {
            $visibleAssigneeIds = [...$visibleAssigneeIds, ...$user->reports()->pluck('id')->all()];
        }

        return $query->where(function (Builder $visible) use ($visibleAssigneeIds): void {
            $visible->whereNull('assigned_to_id')
                ->orWhereIn('assigned_to_id', $visibleAssigneeIds);
        });
    }

    /**
     * Matches a ticket number only when the reporter's tracking token is correct.
     *
     * @param  Builder<Ticket>  $query
     * @return Builder<Ticket>
     */
    public function scopeForTrackingToken(Builder $query, string $ticketNumber, ?string $trackingToken): Builder
    {
        return $query
            ->where('ticket_number', $ticketNumber)
            ->where('tracking_token_hash', hash('sha256', (string) $trackingToken));
    }

    private static function generateTicketNumber(): string
    {
        do {
            $number = 'TCK-'.random_int(10000, 99999);
        } while (self::withTrashed()->where('ticket_number', $number)->exists());

        return $number;
    }

    private static function actorCanCloseOpenTickets(): bool
    {
        $actor = auth()->user();

        if (! $actor instanceof User) {
            return true;
        }

        return $actor->isRole(UserRole::SuperAdmin) || $actor->isRole(UserRole::TeamLead);
    }
}
