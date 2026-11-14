<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $role = $data['role'] instanceof UserRole ? $data['role'] : UserRole::from($data['role']);
        unset($data['role']);

        $user = User::query()->create($data);
        $user->syncRoles([$role->value]);

        return $user;
    }
}
