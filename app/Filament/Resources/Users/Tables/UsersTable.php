<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                    ->state(fn (User $record): ?string => $record->getRoleNames()->first())
                    ->formatStateUsing(fn (?string $state): ?string => UserRole::tryFrom($state ?? '')?->getLabel())
                    ->badge(),
                TextColumn::make('reportsTo.name')
                    ->label(__('filament/resources/users.fields.reports_to'))
                    ->placeholder(__('filament/resources/users.no_manager')),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label(__('filament/resources/users.fields.role'))
                    ->options(UserRole::class)
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas('roles', fn (Builder $roles): Builder => $roles->where('name', $data['value']))
                        : $query),
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
