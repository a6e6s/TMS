<?php

namespace App\Services\Pms;

use App\Models\Comment;
use App\Models\ProjectSync;
use App\Models\Ticket;
use App\Services\Pms\Dto\PmsEventDTO;
use App\Services\Pms\Dto\PmsStatusDTO;
use App\Services\Pms\Dto\PmsTaskDTO;
use Illuminate\Http\Request;

interface PmsConnectorInterface
{
    public function provider(): string;

    public function createTask(Ticket $ticket, ?string $projectId): PmsTaskDTO;

    public function updateTask(ProjectSync $sync, Ticket $ticket): void;

    public function fetchTaskStatus(ProjectSync $sync): PmsStatusDTO;

    public function postComment(ProjectSync $sync, Comment $comment): void;

    public function parseWebhook(Request $request): PmsEventDTO;
}
