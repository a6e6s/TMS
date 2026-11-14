<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserRole;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament/resources/users.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('filament/resources/users.fields.email'))
                    ->searchable(),
                TextColumn::make('role')
                    ->label(__('filament/resources/users.fields.role'))
                    ->badge(),
                TextColumn::make('reportsTo.name')
                    ->label(__('filament/resources/users.fields.reports_to'))
                    ->placeholder(__('filament/resources/users.no_manager')),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label(__('filament/resources/users.fields.role'))
                    ->options(UserRole::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
