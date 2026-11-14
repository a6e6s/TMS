<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $this->isVisibleTo($user, $ticket);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $this->isVisibleTo($user, $ticket);
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }

    public function restore(User $user, Ticket $ticket): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }

    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }

    private function isVisibleTo(User $user, Ticket $ticket): bool
    {
        return Ticket::query()
            ->visibleTo($user)
            ->whereKey($ticket->getKey())
            ->exists();
    }
}
