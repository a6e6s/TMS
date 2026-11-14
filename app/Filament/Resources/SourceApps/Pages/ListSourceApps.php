<?php

namespace App\Filament\Resources\SourceApps\Pages;

use App\Filament\Resources\SourceApps\SourceAppResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSourceApps extends ListRecords
{
    protected static string $resource = SourceAppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
