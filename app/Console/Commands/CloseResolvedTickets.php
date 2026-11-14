<?php

namespace App\Console\Commands;

use App\Enums\TicketStatus;
use App\Models\Setting;
use App\Models\Ticket;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Signature('tickets:close-resolved')]
#[Description('Close resolved tickets that have not been reopened within the auto-close window')]
class CloseResolvedTickets extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) Setting::get('ticket.auto_close_days', 14);
        $cutoff = Carbon::now()->subDays($days);

        $closed = 0;

        Ticket::query()
            ->where('status', TicketStatus::Resolved)
            ->where('updated_at', '<=', $cutoff)
            ->chunkById(100, function ($tickets) use (&$closed): void {
                foreach ($tickets as $ticket) {
                    $ticket->update(['status' => TicketStatus::Closed]);
                    $closed++;
                }
            });

        $this->info("Closed {$closed} resolved ticket(s).");

        return self::SUCCESS;
    }
}
