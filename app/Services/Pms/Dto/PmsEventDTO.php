<?php

namespace App\Services\Pms\Dto;

readonly class PmsEventDTO
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $eventId,
        public string $externalTaskId,
        public string $statusCategory,
        public array $payload,
    ) {}
}
