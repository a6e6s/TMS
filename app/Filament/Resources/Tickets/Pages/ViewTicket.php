<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Enums\TicketStatus;
use App\Filament\Resources\Tickets\TicketResource;
use App\Jobs\PushTicketToPmsJob;
use App\Models\Ticket;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pushToPms')
                ->label(__('filament/resources/tickets.pms.push_action'))
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->color('primary')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->canPushToPms())
                ->action(function (): void {
                    $ticket = $this->getRecord();

                    if (! $ticket instanceof Ticket) {
                        return;
                    }

                    PushTicketToPmsJob::dispatch($ticket->getKey(), (string) config('pms.default_provider'));

                    Notification::make()
                        ->title(__('filament/resources/tickets.pms.queued'))
                        ->success()
                        ->send();
                }),
            EditAction::make(),
        ];
    }

    private function canPushToPms(): bool
    {
        $ticket = $this->getRecord();

        if (! $ticket instanceof Ticket || $ticket->status === TicketStatus::Closed) {
            return false;
        }

        return ! $ticket->projectSyncs()
            ->where('pms_provider', (string) config('pms.default_provider'))
            ->whereNotNull('external_task_id')
            ->exists();
    }
}
