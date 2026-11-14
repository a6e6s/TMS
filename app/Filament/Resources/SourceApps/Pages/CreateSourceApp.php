<?php

namespace App\Filament\Resources\SourceApps\Pages;

use App\Enums\UserType;
use App\Filament\Resources\SourceApps\SourceAppResource;
use App\Models\SourceApp;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreateSourceApp extends CreateRecord
{
    protected static string $resource = SourceAppResource::class;

    /**
     * Each source app gets its own service account, which owns its Sanctum tokens.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $serviceUser = User::query()->create([
            'name' => $data['name'].' (service)',
            'email' => $data['slug'].'@services.invalid',
            'password' => Str::random(64),
            'user_type' => UserType::Service,
        ]);

        return SourceApp::query()->create([
            ...$data,
            'user_id' => $serviceUser->getKey(),
        ]);
    }
}
