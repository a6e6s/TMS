<?php

namespace App\Services\Pms;

use App\Exceptions\ApiProblemException;
use App\Models\Comment;
use App\Models\ProjectSync;
use App\Models\Ticket;
use App\Services\Pms\Dto\PmsEventDTO;
use App\Services\Pms\Dto\PmsStatusDTO;
use App\Services\Pms\Dto\PmsTaskDTO;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Generic REST connector. Endpoints: POST /tasks, PATCH /tasks/{id},
 * GET /tasks/{id}, POST /tasks/{id}/comments.
 */
class GenericHttpPmsConnector implements PmsConnectorInterface
{
    public function provider(): string
    {
        return 'custom';
    }

    public function createTask(Ticket $ticket, ?string $projectId): PmsTaskDTO
    {
        $response = $this->client()->post('/tasks', [
            'project_id' => $projectId ?? config('pms.default_project_id'),
            'title' => $ticket->title,
            'description' => $ticket->description,
            'external_reference' => $ticket->ticket_number,
            'priority' => $ticket->priority->value,
        ])->throw();

        return new PmsTaskDTO(
            externalId: (string) $response->json('id'),
            url: $response->json('url'),
        );
    }

    public function updateTask(ProjectSync $sync, Ticket $ticket): void
    {
        $this->client()->patch('/tasks/'.$sync->external_task_id, [
            'status' => $ticket->status->value,
            'priority' => $ticket->priority->value,
        ])->throw();
    }

    public function fetchTaskStatus(ProjectSync $sync): PmsStatusDTO
    {
        $response = $this->client()->get('/tasks/'.$sync->external_task_id)->throw();

        return new PmsStatusDTO(category: (string) $response->json('status.category'));
    }

    public function postComment(ProjectSync $sync, Comment $comment): void
    {
        $this->client()->post('/tasks/'.$sync->external_task_id.'/comments', [
            'body' => $comment->body,
        ])->throw();
    }

    public function parseWebhook(Request $request): PmsEventDTO
    {
        $eventId = $request->input('event_id');
        $taskId = $request->input('task_id');
        $category = $request->input('status.category');

        if (! is_string($eventId) || $eventId === '' || ! is_string($taskId) || $taskId === '' || ! is_string($category)) {
            throw new ApiProblemException(422, 'Validation failed', 'event_id, task_id, and status.category are required.');
        }

        return new PmsEventDTO(
            eventId: $eventId,
            externalTaskId: $taskId,
            statusCategory: $category,
            payload: $request->all(),
        );
    }

    private function client(): PendingRequest
    {
        $baseUrl = config('pms.base_url');

        if (! is_string($baseUrl) || $baseUrl === '') {
            throw new RuntimeException('PMS_BASE_URL is not configured.');
        }

        return Http::baseUrl($baseUrl)
            ->withToken((string) config('pms.api_token'))
            ->acceptJson()
            ->timeout((int) config('pms.timeout'));
    }
}
