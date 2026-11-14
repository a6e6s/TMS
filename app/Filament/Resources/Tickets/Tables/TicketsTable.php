<?php

namespace App\Filament\Resources\Tickets\Tables;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_number')
                    ->label(__('filament/resources/tickets.fields.ticket_number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label(__('filament/resources/tickets.fields.title'))
                    ->searchable()
                    ->limit(60),
                TextColumn::make('status')
                    ->label(__('filament/resources/tickets.fields.status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('priority')
                    ->label(__('filament/resources/tickets.fields.priority'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('locale')
                    ->label(__('filament/resources/tickets.fields.locale'))
                    ->badge(),
                TextColumn::make('assignee.name')
                    ->label(__('filament/resources/tickets.fields.assignee'))
                    ->placeholder(__('filament/resources/tickets.unassigned')),
                TextColumn::make('created_at')
                    ->label(__('filament/resources/tickets.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('filament/resources/tickets.fields.status'))
                    ->options(TicketStatus::class),
                SelectFilter::make('priority')
                    ->label(__('filament/resources/tickets.fields.priority'))
                    ->options(TicketPriority::class),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
