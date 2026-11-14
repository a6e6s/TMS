<?php

namespace App\Services\Pms;

use App\Enums\TicketStatus;

class PmsStatusMapper
{
    /**
     * Maps a PMS status category to a ticket status. Unmapped categories return null.
     */
    public function toTicketStatus(string $category): ?TicketStatus
    {
        return match (strtolower($category)) {
            'backlog', 'todo', 'to_do' => TicketStatus::PushedToPms,
            'in_progress', 'in-progress' => TicketStatus::InProgress,
            'done', 'completed' => TicketStatus::Resolved,
            'cancelled', 'canceled', 'wont_fix' => TicketStatus::Closed,
            default => null,
        };
    }
}
