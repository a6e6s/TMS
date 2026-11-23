<?php

namespace App\Actions;

use App\Enums\TicketPriority;
use App\Enums\TicketSource;
use App\Enums\TicketStatus;
use App\Enums\WebhookProcessingStatus;
use App\Exceptions\ApiProblemException;
use App\Models\SourceApp;
use App\Models\Ticket;
use App\Models\WebhookPayload;
use App\Services\PayloadScrubber;
use App\Services\TicketScreenshotStorer;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IngestTicketAction
{
    private const IDEMPOTENCY_WINDOW_HOURS = 24;

    public function __construct(
        private readonly PayloadScrubber $scrubber,
        private readonly TicketScreenshotStorer $screenshots,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     * @return array{ticket: Ticket, tracking_token: ?string, duplicate: bool}
     */
    public function execute(SourceApp $sourceApp, array $validated, Request $request, string $idempotencyKey): array
    {
        $scope = $this->scopeFor($sourceApp);

        $existing = $this->recentPayload($scope, $idempotencyKey);

        if ($existing?->ticket !== null) {
            return ['ticket' => $existing->ticket, 'tracking_token' => null, 'duplicate' => true];
        }

        $trackingToken = Str::random(40);

        try {
            $ticket = DB::transaction(function () use ($sourceApp, $validated, $request, $scope, $idempotencyKey, $trackingToken): Ticket {
                $ticket = $this->createTicket($sourceApp, $validated, $request, $scope, $idempotencyKey, $trackingToken);

                // Stored inside the transaction so a storage failure also rolls back the ticket and the idempotency key.
                $this->screenshots->store($ticket, $validated['screenshots'] ?? []);

                return $ticket;
            });
        } catch (UniqueConstraintViolationException) {
            $existing = $this->recentPayload($scope, $idempotencyKey);

            if ($existing?->ticket !== null) {
                return ['ticket' => $existing->ticket, 'tracking_token' => null, 'duplicate' => true];
            }

            throw $this->conflict();
        }

        return ['ticket' => $ticket, 'tracking_token' => $trackingToken, 'duplicate' => false];
    }

    public function scopeFor(SourceApp $sourceApp): string
    {
        return 'client:'.$sourceApp->getKey();
    }

    /**
     * Keys are honoured for 24 hours. An expired key is released so it can be reused.
     */
    private function recentPayload(string $scope, string $idempotencyKey): ?WebhookPayload
    {
        $payload = WebhookPayload::query()
            ->with('ticket')
            ->where('idempotency_scope', $scope)
            ->where('event_id', $idempotencyKey)
            ->first();

        if ($payload === null) {
            return null;
        }

        if ($payload->created_at?->lt(now()->subHours(self::IDEMPOTENCY_WINDOW_HOURS))) {
            $payload->delete();

            return null;
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function createTicket(
        SourceApp $sourceApp,
        array $validated,
        Request $request,
        string $scope,
        string $idempotencyKey,
        string $trackingToken,
    ): Ticket {
        $reporter = $validated['reporter'] ?? [];
        $deviceMetadata = $this->scrubDeviceMetadata($validated['device_metadata'] ?? null);

        $ticket = Ticket::query()->create([
            'reporter_name' => $reporter['name'] ?? null,
            'reporter_email' => $reporter['email'] ?? null,
            'locale' => $request->getPreferredLanguage(['en', 'ar']) ?? 'en',
            'source' => TicketSource::Api,
            'source_app_id' => $sourceApp->getKey(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'page_url' => $this->scrubber->scrubUrl($validated['page_url'] ?? null),
            'device_metadata' => $deviceMetadata,
            'status' => TicketStatus::Open,
            'priority' => TicketPriority::from($validated['priority'] ?? TicketPriority::Medium->value),
            'tracking_token_hash' => hash('sha256', $trackingToken),
        ]);

        WebhookPayload::query()->create([
            'ticket_id' => $ticket->getKey(),
            'source_app_id' => $sourceApp->getKey(),
            'source' => 'client_api',
            'idempotency_scope' => $scope,
            'event_id' => $idempotencyKey,
            'source_ip' => $request->ip(),
            'headers' => $this->scrubber->allowedHeaders($request->headers->all()),
            'raw_payload' => $this->scrubber->scrub([
                ...collect($validated)->except('screenshots')->all(),
                'screenshots_count' => count($validated['screenshots'] ?? []),
            ]),
            'processing_status' => WebhookProcessingStatus::Processed,
            'attempts' => 1,
        ]);

        return $ticket;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function scrubDeviceMetadata(mixed $metadata): ?array
    {
        if (! is_array($metadata)) {
            return null;
        }

        if (isset($metadata['console_errors']) && is_array($metadata['console_errors'])) {
            $metadata['console_errors'] = $this->scrubber->consoleErrors($metadata['console_errors']);
        }

        return $this->scrubber->scrub($metadata);
    }

    private function conflict(): ApiProblemException
    {
        return new ApiProblemException(409, 'Conflict', 'Idempotency-Key is already in use.');
    }
}
