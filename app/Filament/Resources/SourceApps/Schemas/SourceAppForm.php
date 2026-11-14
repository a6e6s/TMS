<?php

namespace App\Filament\Resources\SourceApps\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SourceAppForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/resources/source-apps.sections.details'))
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label(__('filament/resources/source-apps.fields.name'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (?string $state, Set $set, string $operation): void {
                                    if ($operation === 'create') {
                                        $set('slug', Str::slug((string) $state));
                                    }
                                }),
                            TextInput::make('slug')
                                ->label(__('filament/resources/source-apps.fields.slug'))
                                ->required()
                                ->alphaDash()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                            TextInput::make('pms_project_id')
                                ->label(__('filament/resources/source-apps.fields.pms_project_id'))
                                ->helperText(__('filament/resources/source-apps.help.pms_project_id'))
                                ->maxLength(255),
                            Toggle::make('is_active')
                                ->label(__('filament/resources/source-apps.fields.is_active'))
                                ->default(true),
                        ]),
                    ]),
            ]);
    }
}
