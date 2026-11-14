<?php

namespace App\Filament\Resources\SourceApps\Pages;

use App\Filament\Resources\SourceApps\SourceAppResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSourceApp extends EditRecord
{
    protected static string $resource = SourceAppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
