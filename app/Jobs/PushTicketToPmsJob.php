<?php

namespace App\Jobs;

use App\Enums\ProjectSyncStatus;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\ProjectSync;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\PmsSyncFailedNotification;
use App\Services\Pms\PmsConnectorInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

class PushTicketToPmsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /**
     * @var array<int, int>
     */
    public array $backoff = [60, 300, 1800, 7200, 43200];

    public function __construct(
        public int $ticketId,
        public string $provider,
    ) {}

    public function uniqueId(): string
    {
        return 'pms-push:'.$this->ticketId.':'.$this->provider;
    }

    /**
     * Creates at most one external task per ticket and provider.
     */
    public function handle(PmsConnectorInterface $connector): void
    {
        $ticket = Ticket::query()->with('sourceApp')->find($this->ticketId);

        if ($ticket === null) {
            return;
        }

        $sync = ProjectSync::query()->firstOrCreate(
            ['ticket_id' => $ticket->getKey(), 'pms_provider' => $this->provider],
            ['sync_status' => ProjectSyncStatus::Pending, 'sync_attempts' => 0],
        );

        if ($sync->external_task_id !== null) {
            return;
        }

        $sync->increment('sync_attempts');

        try {
            $task = $connector->createTask($ticket, $ticket->sourceApp?->pms_project_id);
        } catch (Throwable $exception) {
            $sync->update(['last_error' => Str::limit($exception->getMessage(), 1000)]);

            throw $exception;
        }

        $sync->update([
            'external_task_id' => $task->externalId,
            'external_task_url' => $task->url,
            'sync_status' => ProjectSyncStatus::Synced,
            'last_synced_at' => now(),
            'last_error' => null,
        ]);

        if ($ticket->status !== TicketStatus::PushedToPms && $ticket->status->canTransitionTo(TicketStatus::PushedToPms)) {
            $ticket->update(['status' => TicketStatus::PushedToPms]);
        }
    }

    public function failed(?Throwable $exception): void
    {
        $sync = ProjectSync::query()
            ->where('ticket_id', $this->ticketId)
            ->where('pms_provider', $this->provider)
            ->first();

        $sync?->update([
            'sync_status' => ProjectSyncStatus::Failed,
            'last_error' => Str::limit($exception?->getMessage() ?? 'Unknown error', 1000),
        ]);

        $ticket = Ticket::query()->find($this->ticketId);

        if ($ticket === null) {
            return;
        }

        Notification::send(
            User::role(UserRole::SuperAdmin->value)->get(),
            new PmsSyncFailedNotification($ticket->ticket_number, $this->provider),
        );
    }
}
