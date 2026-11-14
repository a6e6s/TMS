<?php

namespace App\Actions;

use App\Enums\CommentAuthorType;
use App\Enums\ProjectSyncStatus;
use App\Enums\TicketStatus;
use App\Models\Comment;
use App\Models\ProjectSync;
use App\Models\TicketActivity;
use App\Services\Pms\Dto\PmsEventDTO;
use App\Services\Pms\PmsStatusMapper;

class ApplyPmsEventAction
{
    public const RESULT_UNKNOWN_TASK = 'unknown_task';

    public const RESULT_PROCESSED = 'processed';

    public const RESULT_IGNORED = 'ignored';

    public function __construct(
        private readonly PmsStatusMapper $statusMapper,
    ) {}

    /**
     * Applies an inbound PMS status event to the linked ticket.
     */
    public function execute(string $provider, PmsEventDTO $event): string
    {
        $sync = ProjectSync::query()
            ->with('ticket')
            ->where('pms_provider', $provider)
            ->where('external_task_id', $event->externalTaskId)
            ->first();

        if ($sync === null || $sync->ticket === null) {
            return self::RESULT_UNKNOWN_TASK;
        }

        $ticket = $sync->ticket;
        $target = $this->statusMapper->toTicketStatus($event->statusCategory);

        if ($target === null) {
            TicketActivity::record($ticket, 'pms_status_unmapped', ['category' => $event->statusCategory]);

            return self::RESULT_IGNORED;
        }

        if (! $ticket->status->canTransitionTo($target)) {
            TicketActivity::record($ticket, 'pms_status_ignored', [
                'category' => $event->statusCategory,
                'from' => $ticket->status->value,
                'to' => $target->value,
            ]);

            return self::RESULT_IGNORED;
        }

        if ($ticket->status !== $target) {
            $ticket->update(['status' => $target]);
        }

        $sync->update([
            'sync_status' => ProjectSyncStatus::Synced,
            'last_synced_at' => now(),
            'last_error' => null,
        ]);

        $this->announceCompletion($ticket->getKey(), $ticket->locale->value, $target);

        return self::RESULT_PROCESSED;
    }

    private function announceCompletion(int $ticketId, string $locale, TicketStatus $target): void
    {
        $message = match ($target) {
            TicketStatus::Resolved => __('filament/resources/tickets.pms.completed', [], $locale),
            TicketStatus::Closed => __('filament/resources/tickets.pms.cancelled', [], $locale),
            default => null,
        };

        if ($message === null) {
            return;
        }

        Comment::query()->create([
            'ticket_id' => $ticketId,
            'author_type' => CommentAuthorType::System,
            'body' => $message,
            'is_internal' => false,
        ]);
    }
}
