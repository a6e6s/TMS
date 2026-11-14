<?php

namespace App\Services\Pms\Dto;

readonly class PmsTaskDTO
{
    public function __construct(
        public string $externalId,
        public ?string $url,
    ) {}
}
