<?php

namespace App\Services;

/**
 * Removes secrets and personal data before any intake payload is persisted.
 */
class PayloadScrubber
{
    private const SECRET_KEY_FRAGMENTS = [
        'password', 'token', 'secret', 'authorization', 'cookie', 'credit_card', 'cvv', 'api_key',
    ];

    private const ALLOWED_HEADERS = ['user-agent', 'accept-language', 'content-type', 'x-request-id'];

    private const MAX_CONSOLE_ERROR_LENGTH = 1000;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function scrub(array $payload): array
    {
        return $this->removeSecretKeys($payload);
    }

    /**
     * @param  array<string, array<int, string>>  $headers
     * @return array<string, string|null>
     */
    public function allowedHeaders(array $headers): array
    {
        $allowed = [];

        foreach ($headers as $name => $values) {
            $normalized = strtolower($name);

            if (in_array($normalized, self::ALLOWED_HEADERS, true)) {
                $allowed[$normalized] = $values[0] ?? null;
            }
        }

        return $allowed;
    }

    /**
     * @param  array<int, mixed>  $errors
     * @return array<int, string>
     */
    public function consoleErrors(array $errors): array
    {
        return collect($errors)
            ->filter(fn (mixed $error): bool => is_string($error) && $error !== '')
            ->map(fn (string $error): string => $this->redactText(mb_substr($error, 0, self::MAX_CONSOLE_ERROR_LENGTH)))
            ->values()
            ->all();
    }

    public function scrubUrl(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        $parts = parse_url($url);

        if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
            return $url;
        }

        $query = [];
        parse_str($parts['query'] ?? '', $query);
        $query = $this->removeSecretKeys($query);

        $rebuilt = $parts['scheme'].'://'.$parts['host'];

        if (isset($parts['port'])) {
            $rebuilt .= ':'.$parts['port'];
        }

        $rebuilt .= $parts['path'] ?? '';

        if ($query !== []) {
            $rebuilt .= '?'.http_build_query($query);
        }

        return $rebuilt.(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    private function removeSecretKeys(array $data): array
    {
        $clean = [];

        foreach ($data as $key => $value) {
            if (is_string($key) && $this->isSecretKey($key)) {
                continue;
            }

            $clean[$key] = is_array($value) ? $this->removeSecretKeys($value) : $value;
        }

        return $clean;
    }

    private function isSecretKey(string $key): bool
    {
        $normalized = strtolower($key);

        foreach (self::SECRET_KEY_FRAGMENTS as $fragment) {
            if (str_contains($normalized, $fragment)) {
                return true;
            }
        }

        return false;
    }

    private function redactText(string $text): string
    {
        $text = preg_replace('/Bearer\s+[A-Za-z0-9._\-]+/i', 'Bearer [redacted]', $text) ?? $text;

        return preg_replace('/[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}/', '[email]', $text) ?? $text;
    }
}
