<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/resources/users.sections.account'))
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label(__('filament/resources/users.fields.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('email')
                                ->label(__('filament/resources/users.fields.email'))
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                            TextInput::make('password')
                                ->label(__('filament/resources/users.fields.password'))
                                ->password()
                                ->revealable()
                                ->required(fn (string $operation): bool => $operation === 'create')
                                ->dehydrated(fn (?string $state): bool => filled($state))
                                ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                                ->helperText(__('filament/resources/users.password_help'))
                                ->minLength(8),
                        ]),
                    ]),
                Section::make(__('filament/resources/users.sections.access'))
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('role')
                                ->label(__('filament/resources/users.fields.role'))
                                ->options(UserRole::class)
                                ->default(UserRole::SupportAgent->value)
                                ->afterStateHydrated(function (Select $component, ?User $record): void {
                                    $component->state($record?->getRoleNames()->first());
                                })
                                ->required(),
                            Select::make('reports_to_id')
                                ->label(__('filament/resources/users.fields.reports_to'))
                                ->relationship('reportsTo', 'name')
                                ->searchable()
                                ->preload()
                                ->placeholder(__('filament/resources/users.no_manager'))
                                ->rules(fn (?User $record): array => $record instanceof User
                                    ? [Rule::notIn([$record->getKey()])]
                                    : []),
                        ]),
                    ]),
            ]);
    }
}
