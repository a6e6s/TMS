<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\ApplyPmsEventAction;
use App\Enums\WebhookProcessingStatus;
use App\Exceptions\ApiProblemException;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\WebhookPayload;
use App\Services\PayloadScrubber;
use App\Services\Pms\PmsConnectorInterface;
use App\Services\Pms\PmsWebhookVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class PmsWebhookController extends Controller
{
    public function store(
        Request $request,
        string $provider,
        PmsConnectorInterface $connector,
        PmsWebhookVerifier $verifier,
        PayloadScrubber $scrubber,
        ApplyPmsEventAction $applyEvent,
    ): JsonResponse {
        if ($provider !== $connector->provider()) {
            throw new ApiProblemException(404, 'Not Found', 'Unknown PMS provider.');
        }

        $secret = $this->webhookSecret($provider);
        $rawBody = $request->getContent();
        $timestamp = (string) $request->header('X-PMS-Timestamp', '');
        $signature = (string) $request->header('X-PMS-Signature', '');

        if (! ctype_digit($timestamp)
            || ! $verifier->isFresh((int) $timestamp, (int) config('pms.webhook_tolerance_seconds'))
        ) {
            throw new ApiProblemException(401, 'Unauthorized', 'Stale or missing webhook timestamp.');
        }

        if (! $verifier->hasValidSignature($timestamp, $rawBody, $signature, $secret)) {
            throw new ApiProblemException(401, 'Unauthorized', 'Invalid webhook signature.');
        }

        $event = $connector->parseWebhook($request);
        $scope = 'pms:'.$provider;

        $alreadyProcessed = WebhookPayload::query()
            ->where('idempotency_scope', $scope)
            ->where('event_id', $event->eventId)
            ->exists();

        if ($alreadyProcessed) {
            return response()->json(['status' => 'duplicate'], 200);
        }

        $payload = WebhookPayload::query()->create([
            'source' => 'pms_webhook',
            'idempotency_scope' => $scope,
            'event_id' => $event->eventId,
            'source_ip' => $request->ip(),
            'headers' => $scrubber->allowedHeaders($request->headers->all()),
            'raw_payload' => $scrubber->scrub($event->payload),
            'processing_status' => WebhookProcessingStatus::Pending,
            'attempts' => 1,
        ]);

        try {
            $result = $applyEvent->execute($provider, $event);
        } catch (Throwable $exception) {
            $payload->update([
                'processing_status' => WebhookProcessingStatus::Failed,
                'error_message' => mb_substr($exception->getMessage(), 0, 1000),
            ]);

            throw $exception;
        }

        $payload->update([
            'processing_status' => WebhookProcessingStatus::Processed,
            'error_message' => $result === ApplyPmsEventAction::RESULT_UNKNOWN_TASK ? 'Unknown task.' : null,
        ]);

        return response()->json(
            ['status' => $result],
            $result === ApplyPmsEventAction::RESULT_UNKNOWN_TASK ? 202 : 200,
        );
    }

    private function webhookSecret(string $provider): string
    {
        $stored = Setting::get('pms.webhook_secret.'.$provider);

        if (! is_string($stored) || $stored === '') {
            throw new ApiProblemException(401, 'Unauthorized', 'Webhook secret is not configured.');
        }

        return Crypt::decryptString($stored);
    }
}
