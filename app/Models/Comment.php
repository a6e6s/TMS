<?php

namespace App\Models;

use App\Enums\CommentAuthorType;
use App\Jobs\SyncTicketChangeToPmsJob;
use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $ticket_id
 * @property int|null $author_id
 * @property CommentAuthorType $author_type
 * @property bool $is_internal
 */
#[Fillable(['ticket_id', 'parent_id', 'author_id', 'author_type', 'body', 'is_internal', 'attachments'])]
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::created(function (Comment $comment): void {
            if ($comment->is_internal || $comment->author_type === CommentAuthorType::System) {
                return;
            }

            if ($comment->ticket->projectSyncs()->exists()) {
                SyncTicketChangeToPmsJob::dispatch($comment->ticket_id, $comment->getKey());
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
            'author_type' => CommentAuthorType::class,
            'attachments' => 'array',
        ];
    }

    /**
     * @param  Builder<Comment>  $query
     * @return Builder<Comment>
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_internal', false);
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
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return BelongsTo<Comment, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
