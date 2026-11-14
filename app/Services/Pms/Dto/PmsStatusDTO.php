<?php

namespace App\Services\Pms\Dto;

readonly class PmsStatusDTO
{
    public function __construct(
        public string $category,
    ) {}
}
