<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CommentAuthorType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreClientCommentRequest;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;

class ClientCommentController extends Controller
{
    public function __construct(
        private readonly ClientTicketController $tickets,
    ) {}

    /**
     * A reporter replies on their ticket. The reply is public and queued for the PMS task.
     */
    public function store(StoreClientCommentRequest $request, string $ticketNumber): JsonResponse
    {
        $ticket = $this->tickets->findTicket($request, $ticketNumber);

        $comment = Comment::query()->create([
            'ticket_id' => $ticket->getKey(),
            'author_type' => CommentAuthorType::Client,
            'body' => $request->validated('body'),
            'is_internal' => false,
        ]);

        return response()->json([
            'status' => 'queued',
            'comment_id' => $comment->getKey(),
        ], 202);
    }
}
