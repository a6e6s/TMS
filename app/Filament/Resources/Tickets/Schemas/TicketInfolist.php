<?php

namespace App\Filament\Resources\Tickets\Schemas;

use App\Models\Ticket;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\URL;

class TicketInfolist
{
    /**
     * @return array<int, string>
     */
    private static function screenshotPaths(Ticket $record): array
    {
        return array_values(array_filter(
            $record->attachments ?? [],
            fn (string $path): bool => preg_match('/\.(png|jpe?g|webp)$/i', $path) === 1,
        ));
    }

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
                        ]),
                        TextEntry::make('title')
                            ->label(__('filament/resources/tickets.fields.title'))
                            ->columnSpanFull(),
                        TextEntry::make('description')
                            ->label(__('filament/resources/tickets.fields.description'))
                            ->columnSpanFull(),
                    ]),
                Section::make(__('filament/resources/tickets.sections.browser'))
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('page_url')
                                ->label(__('filament/resources/tickets.fields.page_url'))
                                ->url(fn (?string $state): ?string => $state)
                                ->openUrlInNewTab()
                                ->placeholder('—')
                                ->columnSpanFull(),
                            TextEntry::make('viewport')
                                ->label(__('filament/resources/tickets.fields.viewport'))
                                ->state(fn (Ticket $record): ?string => $record->device_metadata['viewport'] ?? null)
                                ->placeholder('—'),
                            TextEntry::make('user_agent')
                                ->label(__('filament/resources/tickets.fields.user_agent'))
                                ->state(fn (Ticket $record): ?string => $record->device_metadata['user_agent'] ?? null)
                                ->placeholder('—')
                                ->columnSpanFull(),
                            TextEntry::make('console_errors')
                                ->label(__('filament/resources/tickets.fields.console_errors'))
                                ->state(fn (Ticket $record): array => $record->device_metadata['console_errors'] ?? [])
                                ->listWithLineBreaks()
                                ->bulleted()
                                ->placeholder(__('filament/resources/tickets.no_console_errors'))
                                ->columnSpanFull(),
                        ]),
                    ]),
                Section::make(fn (Ticket $record): string => __('filament/resources/tickets.sections.screenshots', ['count' => count(self::screenshotPaths($record))]))
                    ->schema([
                        ViewEntry::make('screenshots')
                            ->hiddenLabel()
                            ->view('filament.infolists.ticket-screenshots')
                            ->viewData(fn (Ticket $record): array => [
                                'shots' => collect(self::screenshotPaths($record))
                                    ->map(fn (string $path, int $index): array => [
                                        'url' => URL::temporarySignedRoute(
                                            'tickets.screenshot',
                                            now()->addMinutes(30),
                                            ['ticket' => $record->getKey(), 'screenshot' => pathinfo($path, PATHINFO_FILENAME)],
                                        ),
                                        'label' => __('filament/resources/tickets.screenshot_label', ['number' => $index + 1]),
                                    ])
                                    ->values()
                                    ->all(),
                            ]),
                    ])
                    ->collapsible(),
                Section::make(__('filament/resources/tickets.sections.ai'))
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('category')
                                ->label(__('filament/resources/tickets.fields.category'))
                                ->badge()
                                ->placeholder(__('filament/resources/tickets.ai.unclassified')),
                            TextEntry::make('ai_priority')
                                ->label(__('filament/resources/tickets.fields.ai_priority'))
                                ->badge()
                                ->placeholder('—'),
                            TextEntry::make('ai_confidence')
                                ->label(__('filament/resources/tickets.fields.ai_confidence'))
                                ->formatStateUsing(fn (?string $state): ?string => $state === null ? null : number_format((float) $state * 100).'%')
                                ->placeholder('—'),
                            TextEntry::make('ai_summary')
                                ->label(__('filament/resources/tickets.fields.ai_summary'))
                                ->placeholder('—')
                                ->columnSpanFull(),
                            TextEntry::make('ai_summary_ar')
                                ->label(__('filament/resources/tickets.fields.ai_summary_ar'))
                                ->placeholder('—')
                                ->extraAttributes(['dir' => 'rtl'])
                                ->columnSpanFull(),
                            TextEntry::make('ai_model')
                                ->label(__('filament/resources/tickets.fields.ai_model'))
                                ->placeholder('—'),
                            TextEntry::make('ai_classified_at')
                                ->label(__('filament/resources/tickets.fields.ai_classified_at'))
                                ->dateTime()
                                ->placeholder('—'),
                        ]),
                    ])
                    ->collapsible(),
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
                Section::make(__('filament/resources/tickets.sections.pms'))
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('pms_sync_status')
                                ->label(__('filament/resources/tickets.fields.pms_sync_status'))
                                ->state(fn (Ticket $record): ?string => $record->projectSyncs->first()?->sync_status->getLabel())
                                ->badge()
                                ->placeholder('—'),
                            TextEntry::make('pms_last_error')
                                ->label(__('filament/resources/tickets.fields.pms_last_error'))
                                ->state(fn (Ticket $record): ?string => $record->projectSyncs->first()?->last_error)
                                ->placeholder('—'),
                        ]),
                    ])
                    ->visible(fn (Ticket $record): bool => $record->projectSyncs->isNotEmpty()),
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
