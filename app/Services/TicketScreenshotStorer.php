<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TicketScreenshotStorer
{
    private const MAX_BYTES = 2 * 1024 * 1024;

    private const EXTENSIONS = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
    ];

    /**
     * Stores base64 data URIs on the private local disk and records the paths on the ticket.
     * The MIME type is detected from the decoded bytes, never trusted from the data URI prefix.
     *
     * @param  array<int, string>  $dataUris
     */
    public function store(Ticket $ticket, array $dataUris): void
    {
        $paths = [];

        foreach ($dataUris as $dataUri) {
            $binary = $this->decode($dataUri);

            if ($binary === null) {
                continue;
            }

            $extension = self::EXTENSIONS[(new \finfo(FILEINFO_MIME_TYPE))->buffer($binary)] ?? null;

            if ($extension === null) {
                continue;
            }

            $path = 'tickets/'.$ticket->getKey().'/screenshots/'.Str::uuid().'.'.$extension;
            Storage::disk('local')->put($path, $binary);
            $paths[] = $path;
        }

        if ($paths !== []) {
            $ticket->update(['attachments' => [...($ticket->attachments ?? []), ...$paths]]);
        }
    }

    private function decode(string $dataUri): ?string
    {
        if (preg_match('/^data:image\/(png|jpeg|webp);base64,(.+)$/s', $dataUri, $matches) !== 1) {
            return null;
        }

        $binary = base64_decode($matches[2], true);

        if ($binary === false || strlen($binary) > self::MAX_BYTES) {
            return null;
        }

        return $binary;
    }
}
