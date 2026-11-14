<?php

namespace Database\Factories;

use App\Enums\ProjectSyncStatus;
use App\Models\ProjectSync;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectSync>
 */
class ProjectSyncFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'pms_provider' => 'custom',
            'external_task_id' => fake()->unique()->numerify('TASK-#####'),
            'external_task_url' => fake()->url(),
            'sync_status' => ProjectSyncStatus::Synced,
            'last_synced_at' => now(),
            'sync_attempts' => 1,
        ];
    }
}
