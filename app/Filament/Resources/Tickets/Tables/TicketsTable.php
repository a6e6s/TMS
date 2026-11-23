<?php

namespace App\Filament\Resources\Tickets\Tables;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Jobs\TriageTicketJob;
use App\Models\Ticket;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

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
                TextColumn::make('category')
                    ->label(__('filament/resources/tickets.fields.category'))
                    ->badge()
                    ->toggleable()
                    ->placeholder(__('filament/resources/tickets.ai.unclassified')),
                TextColumn::make('ai_priority')
                    ->label(__('filament/resources/tickets.fields.ai_priority'))
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),
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
                SelectFilter::make('category')
                    ->label(__('filament/resources/tickets.fields.category'))
                    ->options(TicketCategory::class),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('rerunAiTriage')
                        ->label(__('filament/resources/tickets.ai.rerun'))
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->visible(fn (): bool => (bool) config('ai.triage.enabled'))
                        ->action(function (Collection $records): void {
                            $records->each(fn (Ticket $ticket) => TriageTicketJob::dispatch($ticket->getKey()));

                            Notification::make()
                                ->title(__('filament/resources/tickets.ai.queued'))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
