<?php

namespace App\Services\Pms;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
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
use Illuminate\Support\Str;
use RuntimeException;

/**
 * FlexFlow (todo.3asry.com) API connector. Auth is the X-API-Key header.
 * Tasks live under /v1/projects/{project}/tasks. FlexFlow has no comment
 * endpoint and no outbound webhooks, so those operations are rejected.
 */
class FlexFlowPmsConnector implements PmsConnectorInterface
{
    public function provider(): string
    {
        return 'flexflow';
    }

    public function createTask(Ticket $ticket, ?string $projectId): PmsTaskDTO
    {
        $response = $this->client()->post($this->tasksPath($this->resolveProjectId($projectId)), [
            'title' => Str::limit($ticket->ticket_number.': '.$ticket->title, 255, ''),
            'description' => $this->description($ticket),
            'priority' => $this->priority($ticket->priority),
            'status' => 'todo',
        ])->throw();

        return new PmsTaskDTO(
            externalId: (string) $response->json('data.id'),
            url: null,
        );
    }

    public function updateTask(ProjectSync $sync, Ticket $ticket): void
    {
        $this->client()->put($this->taskPath($sync), [
            'status' => $this->status($ticket->status),
            'priority' => $this->priority($ticket->priority),
        ])->throw();
    }

    public function fetchTaskStatus(ProjectSync $sync): PmsStatusDTO
    {
        $response = $this->client()->get($this->taskPath($sync))->throw();

        return new PmsStatusDTO(category: (string) $response->json('data.status'));
    }

    public function postComment(ProjectSync $sync, Comment $comment): void
    {
        throw new RuntimeException('FlexFlow does not support task comments; the comment was not sent.');
    }

    public function parseWebhook(Request $request): PmsEventDTO
    {
        throw new ApiProblemException(501, 'Not supported', 'FlexFlow does not send webhooks.');
    }

    private function resolveProjectId(?string $projectId): string
    {
        $projectId ??= config('pms.default_project_id');

        if (! is_string($projectId) || $projectId === '') {
            throw new RuntimeException('The source app has no FlexFlow project ID and PMS_DEFAULT_PROJECT_ID is not set.');
        }

        return $projectId;
    }

    private function tasksPath(string $projectId): string
    {
        return '/v1/projects/'.$projectId.'/tasks';
    }

    private function taskPath(ProjectSync $sync): string
    {
        $ticket = $sync->ticket()->with('sourceApp')->firstOrFail();

        return $this->tasksPath($this->resolveProjectId($ticket->sourceApp?->pms_project_id)).'/'.$sync->external_task_id;
    }

    private function description(Ticket $ticket): string
    {
        $footer = 'Reported in TMS as '.$ticket->ticket_number.'.';

        return $ticket->description ? $ticket->description."\n\n".$footer : $footer;
    }

    /**
     * FlexFlow accepts low, medium, and high only.
     */
    private function priority(TicketPriority $priority): string
    {
        return match ($priority) {
            TicketPriority::Low => 'low',
            TicketPriority::Medium => 'medium',
            TicketPriority::High, TicketPriority::Critical => 'high',
        };
    }

    /**
     * FlexFlow statuses are todo, doing, review, and done.
     */
    private function status(TicketStatus $status): string
    {
        return match ($status) {
            TicketStatus::InProgress => 'doing',
            TicketStatus::Resolved, TicketStatus::Closed => 'done',
            default => 'todo',
        };
    }

    private function client(): PendingRequest
    {
        $baseUrl = config('pms.base_url');

        if (! is_string($baseUrl) || $baseUrl === '') {
            throw new RuntimeException('PMS_BASE_URL is not configured.');
        }

        return Http::baseUrl($baseUrl)
            ->withHeaders(['X-API-Key' => (string) config('pms.api_token')])
            ->acceptJson()
            ->timeout((int) config('pms.timeout'));
    }
}
