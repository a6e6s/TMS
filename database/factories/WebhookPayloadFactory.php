<?php

namespace Database\Factories;

use App\Enums\WebhookProcessingStatus;
use App\Models\WebhookPayload;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WebhookPayload>
 */
class WebhookPayloadFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source' => 'pms_webhook',
            'idempotency_scope' => 'pms:custom',
            'event_id' => fake()->uuid(),
            'source_ip' => fake()->ipv4(),
            'headers' => [],
            'raw_payload' => [],
            'processing_status' => WebhookProcessingStatus::Pending,
            'attempts' => 0,
        ];
    }
}
