<?php

namespace App\Models;

use App\Enums\TicketLocale;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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
    'title',
    'description',
    'page_url',
    'attachments',
    'status',
    'priority',
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
            'locale' => TicketLocale::class,
            'attachments' => 'array',
        ];
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

        return in_array($actor->role, [UserRole::SuperAdmin, UserRole::TeamLead], true);
    }
}
