<?php

namespace App\Exceptions;

use RuntimeException;

class ApiProblemException extends RuntimeException
{
    /**
     * @param  array<string, string>  $headers
     */
    public function __construct(
        public readonly int $status,
        public readonly string $title,
        public readonly ?string $detail = null,
        public readonly array $headers = [],
    ) {
        parent::__construct($detail ?? $title);
    }
}
