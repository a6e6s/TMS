<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Filament\Resources\Tickets\TicketResource;
use App\Jobs\PushTicketToPmsJob;
use App\Jobs\TriageTicketJob;
use App\Models\Ticket;
use App\Models\TicketActivity;
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
            Action::make('applyAiPriority')
                ->label(__('filament/resources/tickets.ai.apply_priority'))
                ->icon(Heroicon::OutlinedSparkles)
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->canApplyAiPriority())
                ->action(function (): void {
                    $ticket = $this->getRecord();

                    if (! $ticket instanceof Ticket || $ticket->ai_priority === null) {
                        return;
                    }

                    $from = $ticket->priority;
                    $to = $ticket->ai_priority;

                    $ticket->update(['priority' => $to]);

                    TicketActivity::record($ticket, 'ai_priority_applied', [
                        'from' => $from->value,
                        'to' => $to->value,
                        'confidence' => $ticket->ai_confidence,
                        'by_staff' => true,
                    ]);

                    Notification::make()
                        ->title(__('filament/resources/tickets.ai.priority_applied'))
                        ->success()
                        ->send();
                }),
            Action::make('rerunAiTriage')
                ->label(__('filament/resources/tickets.ai.rerun'))
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->visible(fn (): bool => (bool) config('ai.triage.enabled'))
                ->action(function (): void {
                    $ticket = $this->getRecord();

                    if (! $ticket instanceof Ticket) {
                        return;
                    }

                    TriageTicketJob::dispatch($ticket->getKey());

                    Notification::make()
                        ->title(__('filament/resources/tickets.ai.queued'))
                        ->success()
                        ->send();
                }),
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

    private function canApplyAiPriority(): bool
    {
        $ticket = $this->getRecord();

        return $ticket instanceof Ticket
            && $ticket->ai_priority instanceof TicketPriority
            && $ticket->ai_priority !== $ticket->priority;
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
