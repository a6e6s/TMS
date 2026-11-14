<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\IngestTicketAction;
use App\Enums\UserType;
use App\Exceptions\ApiProblemException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTicketRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class TicketIngestController extends Controller
{
    /**
     * Accepts a bug report from a connected source app.
     */
    public function store(StoreTicketRequest $request, IngestTicketAction $ingest): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User || $user->user_type !== UserType::Service || ! $user->tokenCan('tickets:create')) {
            throw new ApiProblemException(403, 'Forbidden', 'This token cannot create tickets.');
        }

        $sourceApp = $user->sourceApp;

        if ($sourceApp === null || ! $sourceApp->is_active) {
            throw new ApiProblemException(403, 'Forbidden', 'The source app is inactive.');
        }

        $result = $ingest->execute(
            $sourceApp,
            $request->validated(),
            $request,
            (string) $request->header('Idempotency-Key'),
        );

        $ticket = $result['ticket'];

        $body = [
            'status' => 'queued',
            'ticket_number' => $ticket->ticket_number,
            'tracking_url' => config('tms.tracking_url').'/'.$ticket->ticket_number,
        ];

        if ($result['duplicate']) {
            return response()->json($body, 200);
        }

        $body['tracking_token'] = $result['tracking_token'];

        return response()->json($body, 202);
    }
}
