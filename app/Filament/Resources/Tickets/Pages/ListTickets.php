<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Enums\TicketStatus;
use App\Filament\Resources\Tickets\TicketResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('filament/resources/tickets.tabs.all')),
            'unassigned' => Tab::make(__('filament/resources/tickets.tabs.unassigned'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereNull('assigned_to_id')),
            'open' => Tab::make(__('filament/resources/tickets.tabs.open'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', TicketStatus::Open)),
            'in_progress' => Tab::make(__('filament/resources/tickets.tabs.in_progress'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', TicketStatus::InProgress)),
            'resolved' => Tab::make(__('filament/resources/tickets.tabs.resolved'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', TicketStatus::Resolved)),
        ];
    }
}
