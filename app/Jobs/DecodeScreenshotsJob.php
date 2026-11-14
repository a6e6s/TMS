<?php

namespace App\Jobs;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DecodeScreenshotsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const MAX_BYTES = 2 * 1024 * 1024;

    private const EXTENSIONS = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
    ];

    /**
     * @param  array<int, string>  $screenshots  Base64 data URIs from the intake request.
     */
    public function __construct(
        public int $ticketId,
        public array $screenshots,
    ) {}

    /**
     * Stores each screenshot on the local private disk. The MIME type is detected from
     * the decoded bytes, never trusted from the data URI prefix.
     */
    public function handle(): void
    {
        $ticket = Ticket::query()->find($this->ticketId);

        if ($ticket === null) {
            return;
        }

        $paths = [];

        foreach ($this->screenshots as $dataUri) {
            $binary = $this->decode($dataUri);

            if ($binary === null) {
                continue;
            }

            $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($binary);
            $extension = self::EXTENSIONS[$mime] ?? null;

            if ($extension === null) {
                continue;
            }

            $path = 'tickets/'.$ticket->getKey().'/screenshots/'.Str::uuid().'.'.$extension;
            Storage::disk('local')->put($path, $binary);
            $paths[] = $path;
        }

        if ($paths !== []) {
            $ticket->update([
                'attachments' => [...($ticket->attachments ?? []), ...$paths],
            ]);
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
