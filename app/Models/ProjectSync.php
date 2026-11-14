<?php

namespace App\Models;

use App\Enums\ProjectSyncStatus;
use Database\Factories\ProjectSyncFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $ticket_id
 * @property string $pms_provider
 * @property string|null $external_task_id
 * @property string|null $external_task_url
 * @property ProjectSyncStatus $sync_status
 * @property Carbon|null $last_synced_at
 * @property int $sync_attempts
 * @property string|null $last_error
 */
#[Fillable([
    'ticket_id',
    'pms_provider',
    'external_task_id',
    'external_task_url',
    'sync_status',
    'last_synced_at',
    'sync_attempts',
    'last_error',
])]
class ProjectSync extends Model
{
    /** @use HasFactory<ProjectSyncFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sync_status' => ProjectSyncStatus::class,
            'last_synced_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Ticket, $this>
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
