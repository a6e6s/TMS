<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $ticket_id
 * @property string $event
 * @property array<string, mixed>|null $properties
 * @property Carbon $created_at
 */
#[Fillable(['ticket_id', 'actor_id', 'event', 'properties'])]
class TicketActivity extends Model
{
    public const UPDATED_AT = null;

    /**
     * @param  array<string, mixed>  $properties
     */
    public static function record(Ticket $ticket, string $event, array $properties = []): self
    {
        return self::create([
            'ticket_id' => $ticket->getKey(),
            'actor_id' => auth()->id(),
            'event' => $event,
            'properties' => $properties ?: null,
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Ticket, $this>
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
