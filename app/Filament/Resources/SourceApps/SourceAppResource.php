<?php

namespace App\Filament\Resources\SourceApps;

use App\Filament\Resources\SourceApps\Pages\CreateSourceApp;
use App\Filament\Resources\SourceApps\Pages\EditSourceApp;
use App\Filament\Resources\SourceApps\Pages\ListSourceApps;
use App\Filament\Resources\SourceApps\Schemas\SourceAppForm;
use App\Filament\Resources\SourceApps\Tables\SourceAppsTable;
use App\Models\SourceApp;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SourceAppResource extends Resource
{
    protected static ?string $model = SourceApp::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    public static function getModelLabel(): string
    {
        return __('filament/resources/source-apps.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament/resources/source-apps.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/resources/source-apps.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament/common.navigation_groups.administration');
    }

    public static function form(Schema $schema): Schema
    {
        return SourceAppForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SourceAppsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSourceApps::route('/'),
            'create' => CreateSourceApp::route('/create'),
            'edit' => EditSourceApp::route('/{record}/edit'),
        ];
    }
}
