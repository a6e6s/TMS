<?php

namespace App\Filament\Resources\Tickets\Schemas;

use App\Models\Ticket;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TicketInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/resources/tickets.sections.details'))
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('ticket_number')
                                ->label(__('filament/resources/tickets.fields.ticket_number')),
                            TextEntry::make('status')
                                ->label(__('filament/resources/tickets.fields.status'))
                                ->badge(),
                            TextEntry::make('priority')
                                ->label(__('filament/resources/tickets.fields.priority'))
                                ->badge(),
                            TextEntry::make('locale')
                                ->label(__('filament/resources/tickets.fields.locale'))
                                ->badge(),
                            TextEntry::make('assignee.name')
                                ->label(__('filament/resources/tickets.fields.assignee'))
                                ->placeholder(__('filament/resources/tickets.unassigned')),
                            TextEntry::make('attachments_count')
                                ->label(__('filament/resources/tickets.fields.attachments'))
                                ->state(fn (Ticket $record): int => count($record->attachments ?? [])),
                        ]),
                        TextEntry::make('title')
                            ->label(__('filament/resources/tickets.fields.title'))
                            ->columnSpanFull(),
                        TextEntry::make('description')
                            ->label(__('filament/resources/tickets.fields.description'))
                            ->columnSpanFull(),
                        TextEntry::make('page_url')
                            ->label(__('filament/resources/tickets.fields.page_url'))
                            ->url(fn (?string $state): ?string => $state)
                            ->openUrlInNewTab()
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
                Section::make(__('filament/resources/tickets.sections.reporter'))
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('reporter_name')
                                ->label(__('filament/resources/tickets.fields.reporter_name'))
                                ->placeholder('—'),
                            TextEntry::make('reporter_email')
                                ->label(__('filament/resources/tickets.fields.reporter_email'))
                                ->placeholder('—'),
                        ]),
                    ]),
                Section::make(__('filament/resources/tickets.sections.timeline'))
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('created_at')
                                ->label(__('filament/resources/tickets.fields.created_at'))
                                ->dateTime(),
                            TextEntry::make('updated_at')
                                ->label(__('filament/resources/tickets.fields.updated_at'))
                                ->dateTime(),
                        ]),
                    ]),
            ]);
    }
}
