<?php

use App\Enums\TicketStatus;
use App\Services\Pms\PmsStatusMapper;
use App\Services\Pms\PmsWebhookVerifier;

it('maps PMS status categories to ticket statuses', function (string $category, ?TicketStatus $expected) {
    expect((new PmsStatusMapper)->toTicketStatus($category))->toBe($expected);
})->with([
    ['backlog', TicketStatus::PushedToPms],
    ['todo', TicketStatus::PushedToPms],
    ['in_progress', TicketStatus::InProgress],
    ['done', TicketStatus::Resolved],
    ['completed', TicketStatus::Resolved],
    ['cancelled', TicketStatus::Closed],
    ['wont_fix', TicketStatus::Closed],
    ['someday', null],
]);

it('accepts a timestamp only inside the replay window', function () {
    $verifier = new PmsWebhookVerifier;

    expect($verifier->isFresh(time(), 300))->toBeTrue()
        ->and($verifier->isFresh(time() - 301, 300))->toBeFalse()
        ->and($verifier->isFresh(time() + 301, 300))->toBeFalse();
});

it('verifies HMAC-SHA256 over timestamp and raw body', function () {
    $verifier = new PmsWebhookVerifier;
    $body = '{"event_id":"e1"}';
    $signature = hash_hmac('sha256', '1700000000.'.$body, 'secret');

    expect($verifier->hasValidSignature('1700000000', $body, $signature, 'secret'))->toBeTrue()
        ->and($verifier->hasValidSignature('1700000000', $body.' ', $signature, 'secret'))->toBeFalse()
        ->and($verifier->hasValidSignature('1700000000', $body, $signature, 'other'))->toBeFalse();
});
