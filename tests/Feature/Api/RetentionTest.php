<?php

use App\Enums\TicketStatus;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\WebhookPayload;
use Illuminate\Support\Carbon;

it('deletes webhook payloads older than the retention period only', function () {
    Setting::put('retention.days', 30);

    $old = WebhookPayload::factory()->create(['event_id' => 'old']);
    $old->forceFill(['created_at' => Carbon::now()->subDays(45)])->saveQuietly();

    $recent = WebhookPayload::factory()->create(['event_id' => 'recent']);
    $recent->forceFill(['created_at' => Carbon::now()->subDays(5)])->saveQuietly();

    $this->artisan('payloads:prune')->assertSuccessful();

    expect(WebhookPayload::query()->pluck('event_id')->all())->toBe(['recent']);
});

it('closes tickets through the scheduled command without touching open ones', function () {
    $resolved = Ticket::factory()->create(['status' => TicketStatus::Resolved]);
    $resolved->forceFill(['updated_at' => now()->subDays(30)])->saveQuietly();

    $this->artisan('tickets:close-resolved')->assertSuccessful();

    expect($resolved->refresh()->status)->toBe(TicketStatus::Closed);
});
