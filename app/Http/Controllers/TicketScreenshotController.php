<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketScreenshotController extends Controller
{
    /**
     * Streams one stored screenshot. The path has no file extension on purpose:
     * web servers serve URLs ending in .jpg/.png straight from disk, bypassing Laravel.
     */
    public function __invoke(Ticket $ticket, string $screenshot): StreamedResponse
    {
        $prefix = 'tickets/'.$ticket->getKey().'/screenshots/'.$screenshot.'.';

        $path = collect($ticket->attachments ?? [])
            ->first(fn (string $path): bool => Str::startsWith($path, $prefix));

        abort_if($path === null, 404);

        return Storage::disk('local')->response($path);
    }
}
