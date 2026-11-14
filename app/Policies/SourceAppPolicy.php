<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\SourceApp;
use App\Models\User;

class SourceAppPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isRole(UserRole::SuperAdmin);
    }

    public function view(User $user, SourceApp $sourceApp): bool
    {
        return $user->isRole(UserRole::SuperAdmin);
    }

    public function create(User $user): bool
    {
        return $user->isRole(UserRole::SuperAdmin);
    }

    public function update(User $user, SourceApp $sourceApp): bool
    {
        return $user->isRole(UserRole::SuperAdmin);
    }

    public function delete(User $user, SourceApp $sourceApp): bool
    {
        return $user->isRole(UserRole::SuperAdmin);
    }
}
