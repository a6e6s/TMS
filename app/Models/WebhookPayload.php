<?php

namespace App\Models;

use App\Enums\WebhookProcessingStatus;
use Database\Factories\WebhookPayloadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $ticket_id
 * @property int|null $source_app_id
 * @property string $source
 * @property string $idempotency_scope
 * @property string $event_id
 * @property string|null $source_ip
 * @property array<string, mixed>|null $headers
 * @property array<string, mixed>|null $raw_payload
 * @property WebhookProcessingStatus $processing_status
 * @property int $attempts
 * @property string|null $error_message
 */
#[Fillable([
    'ticket_id',
    'source_app_id',
    'source',
    'idempotency_scope',
    'event_id',
    'source_ip',
    'headers',
    'raw_payload',
    'processing_status',
    'attempts',
    'error_message',
])]
class WebhookPayload extends Model
{
    /** @use HasFactory<WebhookPayloadFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'headers' => 'array',
            'raw_payload' => 'array',
            'processing_status' => WebhookProcessingStatus::class,
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
     * @return BelongsTo<SourceApp, $this>
     */
    public function sourceApp(): BelongsTo
    {
        return $this->belongsTo(SourceApp::class);
    }
}
