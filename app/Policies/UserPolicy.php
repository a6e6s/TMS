<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isRole(UserRole::SuperAdmin) || $user->isRole(UserRole::TeamLead);
    }

    public function view(User $user, User $staff): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isRole(UserRole::SuperAdmin);
    }

    public function update(User $user, User $staff): bool
    {
        return $user->isRole(UserRole::SuperAdmin);
    }

    public function delete(User $user, User $staff): bool
    {
        return $user->isRole(UserRole::SuperAdmin) && $user->isNot($staff);
    }

    public function restore(User $user, User $staff): bool
    {
        return $user->isRole(UserRole::SuperAdmin);
    }

    public function forceDelete(User $user, User $staff): bool
    {
        return false;
    }
}
