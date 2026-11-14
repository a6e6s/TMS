<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::SuperAdmin, UserRole::TeamLead], true);
    }

    public function view(User $user, User $staff): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }

    public function update(User $user, User $staff): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }

    public function delete(User $user, User $staff): bool
    {
        return $user->role === UserRole::SuperAdmin && $user->isNot($staff);
    }

    public function restore(User $user, User $staff): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }

    public function forceDelete(User $user, User $staff): bool
    {
        return false;
    }
}
