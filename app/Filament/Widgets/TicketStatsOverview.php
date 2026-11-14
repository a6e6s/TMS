<?php

namespace App\Filament\Widgets;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class TicketStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return [];
        }

        $visibleTickets = Ticket::query()->visibleTo($user);

        $openCount = (clone $visibleTickets)
            ->whereIn('status', [TicketStatus::Open, TicketStatus::Triaged, TicketStatus::InProgress])
            ->count();

        $resolvedThisWeek = (clone $visibleTickets)
            ->where('status', TicketStatus::Resolved)
            ->where('updated_at', '>=', Carbon::now()->startOfWeek())
            ->count();

        return [
            Stat::make(__('filament/widgets.tickets.open'), $openCount),
            Stat::make(__('filament/widgets.tickets.average_triage_time'), $this->averageTriageTime($user))
                ->description(__('filament/widgets.tickets.average_triage_time_help')),
            Stat::make(__('filament/widgets.tickets.resolved_this_week'), $resolvedThisWeek),
        ];
    }

    /**
     * Average time between ticket creation and each move to "triaged", for tickets the user can see.
     */
    private function averageTriageTime(User $user): string
    {
        $minutes = TicketActivity::query()
            ->with('ticket:id,created_at')
            ->where('event', 'status_changed')
            ->where('properties->to', TicketStatus::Triaged->value)
            ->whereHas('ticket', fn ($query) => $query->visibleTo($user))
            ->latest('created_at')
            ->limit(500)
            ->get()
            ->map(fn (TicketActivity $activity): int => (int) $activity->ticket->created_at->diffInMinutes($activity->created_at));

        if ($minutes->isEmpty()) {
            return '—';
        }

        return __('filament/widgets.tickets.hours', ['hours' => number_format($minutes->avg() / 60, 1)]);
    }
}
