<?php

namespace App\Filament\Resources\Tickets\Schemas;

use App\Enums\TicketLocale;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/resources/tickets.sections.details'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('filament/resources/tickets.fields.title'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label(__('filament/resources/tickets.fields.description'))
                            ->required()
                            ->rows(6)
                            ->columnSpanFull(),
                        TextInput::make('page_url')
                            ->label(__('filament/resources/tickets.fields.page_url'))
                            ->url()
                            ->maxLength(2048)
                            ->columnSpanFull(),
                        Select::make('locale')
                            ->label(__('filament/resources/tickets.fields.locale'))
                            ->options(TicketLocale::class)
                            ->default(TicketLocale::English)
                            ->required(),
                        FileUpload::make('attachments')
                            ->label(__('filament/resources/tickets.fields.attachments'))
                            ->multiple()
                            ->maxFiles(5)
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'application/pdf'])
                            ->disk('local')
                            ->directory('tickets/attachments')
                            ->visibility('private')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make(__('filament/resources/tickets.sections.reporter'))
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('reporter_name')
                                ->label(__('filament/resources/tickets.fields.reporter_name'))
                                ->maxLength(255),
                            TextInput::make('reporter_email')
                                ->label(__('filament/resources/tickets.fields.reporter_email'))
                                ->email()
                                ->maxLength(255),
                        ]),
                    ]),
                Section::make(__('filament/resources/tickets.sections.triage'))
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('status')
                                ->label(__('filament/resources/tickets.fields.status'))
                                ->options(fn (?Ticket $record): array => self::statusOptions($record))
                                ->default(TicketStatus::Open)
                                ->required(),
                            Select::make('priority')
                                ->label(__('filament/resources/tickets.fields.priority'))
                                ->options(TicketPriority::class)
                                ->default(TicketPriority::Medium)
                                ->required(),
                            Select::make('assigned_to_id')
                                ->label(__('filament/resources/tickets.fields.assignee'))
                                ->relationship('assignee', 'name')
                                ->searchable()
                                ->preload()
                                ->placeholder(__('filament/resources/tickets.unassigned')),
                        ]),
                    ]),
            ]);
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(?Ticket $record): array
    {
        if (! $record instanceof Ticket) {
            return [TicketStatus::Open->value => TicketStatus::Open->getLabel()];
        }

        $current = $record->status;

        return collect([$current, ...$current->allowedTransitions()])
            ->mapWithKeys(fn (TicketStatus $status): array => [$status->value => $status->getLabel()])
            ->all();
    }
}
