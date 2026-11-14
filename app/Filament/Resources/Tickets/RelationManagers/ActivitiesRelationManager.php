<?php

namespace App\Filament\Resources\Tickets\RelationManagers;

use App\Models\TicketActivity;
use App\Models\User;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament/resources/tickets.activities.title');
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event')
                    ->label(__('filament/resources/tickets.activities.fields.event'))
                    ->formatStateUsing(fn (string $state): string => __('filament/resources/tickets.activities.events.'.$state)),
                TextColumn::make('change')
                    ->label(__('filament/resources/tickets.activities.fields.change'))
                    ->state(fn (TicketActivity $record): ?string => self::describeChange($record))
                    ->placeholder('—'),
                TextColumn::make('actor.name')
                    ->label(__('filament/resources/tickets.activities.fields.actor'))
                    ->placeholder(__('filament/resources/tickets.activities.system')),
                TextColumn::make('created_at')
                    ->label(__('filament/resources/tickets.fields.created_at'))
                    ->dateTime(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private static function describeChange(TicketActivity $record): ?string
    {
        $properties = $record->properties ?? [];

        if ($properties === []) {
            return null;
        }

        return __('filament/resources/tickets.activities.change', [
            'from' => self::displayValue($record->event, $properties['from'] ?? null),
            'to' => self::displayValue($record->event, $properties['to'] ?? null),
        ]);
    }

    private static function displayValue(string $event, mixed $value): string
    {
        if ($value === null || $value === '') {
            return __('filament/resources/tickets.unassigned');
        }

        return match ($event) {
            'status_changed' => __('filament/enums.ticket_status.'.$value),
            'priority_changed' => __('filament/enums.ticket_priority.'.$value),
            'assignee_changed' => User::query()->whereKey($value)->value('name') ?? (string) $value,
            default => (string) $value,
        };
    }
}
