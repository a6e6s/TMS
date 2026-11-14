<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\ApiProblemException;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientTicketController extends Controller
{
    /**
     * Ticket status for the reporter. Internal notes are never included.
     */
    public function show(Request $request, string $ticketNumber): JsonResponse
    {
        $ticket = $this->findTicket($request, $ticketNumber);

        $ticket->load(['projectSyncs', 'comments' => fn ($query) => $query->public()->oldest()]);

        return response()->json([
            'ticket_number' => $ticket->ticket_number,
            'title' => $ticket->title,
            'status' => $ticket->status->value,
            'priority' => $ticket->priority->value,
            'locale' => $ticket->locale->value,
            'created_at' => $ticket->created_at?->toIso8601String(),
            'updated_at' => $ticket->updated_at?->toIso8601String(),
            'sync' => $ticket->projectSyncs->map(fn ($sync): array => [
                'state' => $sync->sync_status->value,
            ])->values(),
            'comments' => $ticket->comments->map(fn (Comment $comment): array => [
                'body' => $comment->body,
                'author' => $comment->author_type->value,
                'created_at' => $comment->created_at?->toIso8601String(),
            ])->values(),
        ]);
    }

    /**
     * Keeps the tracking token lookup in one place for the client endpoints.
     */
    public function findTicket(Request $request, string $ticketNumber): Ticket
    {
        $ticket = Ticket::query()
            ->forTrackingToken($ticketNumber, $request->header('X-Tracking-Token'))
            ->first();

        if ($ticket === null) {
            throw new ApiProblemException(404, 'Not Found', 'Ticket not found.');
        }

        return $ticket;
    }
}
