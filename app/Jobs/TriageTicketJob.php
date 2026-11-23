<?php

namespace App\Jobs;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Services\Ai\TicketTriageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

/**
 * Classifies a new ticket with AI. Only raises priority to critical, and only when the model is highly confident.
 */
class TriageTicketJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [60, 300, 1800];

    public function __construct(public int $ticketId) {}

    public function handle(TicketTriageService $triage): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $ticket = Ticket::query()->find($this->ticketId);

        if ($ticket === null) {
            return;
        }

        $result = $triage->classify($ticket);

        if ($result === null) {
            $ticket->update([
                'category' => TicketCategory::Unclear,
                'ai_model' => config('ai.triage.model'),
                'ai_classified_at' => now(),
            ]);

            TicketActivity::record($ticket, 'ai_triage_no_result');

            return;
        }

        $ticket->update([
            'category' => $result['category'],
            'ai_priority' => $result['ai_priority'],
            'ai_confidence' => $result['confidence'],
            'ai_summary' => $result['summary_en'],
            'ai_summary_ar' => $result['summary_ar'] ?: null,
            'ai_model' => $result['model'],
            'ai_classified_at' => now(),
        ]);

        $this->raiseToCriticalIfConfident($ticket, $result['ai_priority'], $result['confidence']);
    }

    public function failed(Throwable $exception): void
    {
        report($exception);

        $ticket = Ticket::query()->find($this->ticketId);

        if ($ticket !== null) {
            TicketActivity::record($ticket, 'ai_triage_failed', [
                'message' => Str::limit($exception->getMessage(), 500),
            ]);
        }
    }

    private function raiseToCriticalIfConfident(Ticket $ticket, TicketPriority $aiPriority, float $confidence): void
    {
        if ($aiPriority !== TicketPriority::Critical
            || $confidence < (float) config('ai.triage.auto_critical_min_confidence')
            || $ticket->priority === TicketPriority::Critical) {
            return;
        }

        $from = $ticket->priority;

        $ticket->update(['priority' => TicketPriority::Critical]);

        TicketActivity::record($ticket, 'ai_priority_applied', [
            'from' => $from->value,
            'to' => TicketPriority::Critical->value,
            'confidence' => $confidence,
        ]);

        // The model's updated hook only syncs staff changes, and queued jobs have no authenticated user.
        if ($ticket->projectSyncs()->exists()) {
            SyncTicketChangeToPmsJob::dispatch($ticket->getKey());
        }
    }

    private function isConfigured(): bool
    {
        return (bool) config('ai.triage.enabled')
            && filled(config('ai.triage.model'))
            && filled(config('ai.triage.api_key'));
    }
}
