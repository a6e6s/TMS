<?php

namespace App\Http\Requests\Api;

use App\Exceptions\ApiProblemException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreTicketRequest extends FormRequest
{
    private const MAX_PAYLOAD_BYTES = 5 * 1024 * 1024;

    private const MAX_SCREENSHOT_BYTES = 2 * 1024 * 1024;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Enforces payload size limits before validation runs.
     */
    protected function prepareForValidation(): void
    {
        if ((int) $this->header('Content-Length', '0') > self::MAX_PAYLOAD_BYTES) {
            throw new ApiProblemException(413, 'Payload Too Large', 'The request exceeds the 5 MB limit.');
        }

        foreach ((array) $this->input('screenshots', []) as $screenshot) {
            if (is_string($screenshot) && $this->decodedSize($screenshot) > self::MAX_SCREENSHOT_BYTES) {
                throw new ApiProblemException(413, 'Payload Too Large', 'Each screenshot must be 2 MB or smaller.');
            }
        }
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'priority' => ['nullable', 'in:low,medium,high,critical'],
            'page_url' => ['nullable', 'url', 'max:2048'],
            'reporter' => ['nullable', 'array'],
            'reporter.name' => ['nullable', 'string', 'max:255'],
            'reporter.email' => ['nullable', 'email', 'max:255'],
            'device_metadata' => ['nullable', 'array'],
            'device_metadata.console_errors' => ['nullable', 'array', 'max:50'],
            'device_metadata.console_errors.*' => ['string'],
            'screenshots' => ['nullable', 'array', 'max:5'],
            'screenshots.*' => [
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value) || preg_match('/^data:image\/(png|jpeg|webp);base64,/', $value) !== 1) {
                        $fail('Each screenshot must be a PNG, JPEG, or WebP data URI.');
                    }
                },
            ],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! is_string($this->header('Idempotency-Key')) || $this->header('Idempotency-Key') === '') {
                    $validator->errors()->add('Idempotency-Key', 'The Idempotency-Key header is required.');
                }
            },
        ];
    }

    private function decodedSize(string $dataUri): int
    {
        $payload = substr($dataUri, (int) strpos($dataUri, ',') + 1);

        return (int) (strlen($payload) * 3 / 4);
    }
}
