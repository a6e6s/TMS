<?php

namespace App\Services\Pms;

class PmsWebhookVerifier
{
    public function isFresh(int $timestamp, int $toleranceSeconds): bool
    {
        return abs(time() - $timestamp) <= $toleranceSeconds;
    }

    /**
     * HMAC-SHA256 over "{timestamp}.{raw body}".
     */
    public function hasValidSignature(string $timestamp, string $rawBody, string $signature, string $secret): bool
    {
        $expected = hash_hmac('sha256', $timestamp.'.'.$rawBody, $secret);

        return hash_equals($expected, $signature);
    }
}
