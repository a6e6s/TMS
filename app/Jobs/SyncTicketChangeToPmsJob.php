<?php

namespace App\Jobs;

use App\Enums\ProjectSyncStatus;
use App\Models\Comment;
use App\Models\ProjectSync;
use App\Models\Ticket;
use App\Services\Pms\PmsConnectorInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

/**
 * Outbound sync: field updates (status, priority) and public comments to the PMS task.
 * Internal notes are never sent.
 */
class SyncTicketChangeToPmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /**
     * @var array<int, int>
     */
    public array $backoff = [60, 300, 1800, 7200, 43200];

    public function __construct(
        public int $ticketId,
        public ?int $commentId = null,
    ) {}

    public function handle(PmsConnectorInterface $connector): void
    {
        $ticket = Ticket::query()->find($this->ticketId);

        if ($ticket === null) {
            return;
        }

        $sync = ProjectSync::query()
            ->where('ticket_id', $ticket->getKey())
            ->where('pms_provider', $connector->provider())
            ->whereNotNull('external_task_id')
            ->first();

        if ($sync === null) {
            return;
        }

        if ($this->commentId !== null) {
            $comment = Comment::query()->find($this->commentId);

            if ($comment === null || $comment->is_internal) {
                return;
            }

            $connector->postComment($sync, $comment);
        } else {
            $connector->updateTask($sync, $ticket);
        }

        $sync->update([
            'sync_status' => ProjectSyncStatus::Synced,
            'last_synced_at' => now(),
            'last_error' => null,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        ProjectSync::query()
            ->where('ticket_id', $this->ticketId)
            ->update([
                'sync_status' => ProjectSyncStatus::OutOfSync,
                'last_error' => Str::limit($exception?->getMessage() ?? 'Unknown error', 1000),
            ]);
    }
}
