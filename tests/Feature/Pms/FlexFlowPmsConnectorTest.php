<?php

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\ProjectSync;
use App\Models\SourceApp;
use App\Models\Ticket;
use App\Services\Pms\FlexFlowPmsConnector;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'pms.base_url' => 'https://todo.test/api',
        'pms.api_token' => 'flex-key',
        'pms.default_project_id' => null,
    ]);
});

it('creates a FlexFlow task in the source app project with the API key header', function () {
    Http::fake(['todo.test/api/v1/projects/7/tasks' => Http::response(['data' => ['id' => 42]], 201)]);

    $ticket = Ticket::factory()->create([
        'title' => 'Login fails',
        'description' => 'Steps to reproduce',
        'priority' => TicketPriority::Critical,
    ]);

    $task = (new FlexFlowPmsConnector)->createTask($ticket, '7');

    expect($task->externalId)->toBe('42');

    Http::assertSent(function ($request) use ($ticket) {
        return $request->url() === 'https://todo.test/api/v1/projects/7/tasks'
            && $request->hasHeader('X-API-Key', 'flex-key')
            && $request['title'] === $ticket->ticket_number.': Login fails'
            && $request['priority'] === 'high'
            && $request['status'] === 'todo'
            && str_contains($request['description'], 'Steps to reproduce');
    });
});

it('refuses to create a task when no FlexFlow project is configured', function () {
    Http::fake();

    $ticket = Ticket::factory()->create();

    expect(fn () => (new FlexFlowPmsConnector)->createTask($ticket, null))
        ->toThrow(RuntimeException::class, 'no FlexFlow project ID');

    Http::assertNothingSent();
});

it('maps TMS ticket status to FlexFlow status when updating a task', function () {
    Http::fake(['todo.test/api/v1/projects/9/tasks/42' => Http::response(['data' => ['id' => 42]], 200)]);

    $sourceApp = SourceApp::factory()->create(['pms_project_id' => '9']);
    $ticket = Ticket::factory()->create([
        'source_app_id' => $sourceApp->getKey(),
        'status' => TicketStatus::InProgress,
    ]);
    $sync = ProjectSync::factory()->create(['ticket_id' => $ticket->getKey(), 'external_task_id' => '42']);

    (new FlexFlowPmsConnector)->updateTask($sync, $ticket);

    Http::assertSent(fn ($request) => $request->method() === 'PUT' && $request['status'] === 'doing');
});
