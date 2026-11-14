<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\WebhookPayload;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Signature('payloads:prune')]
#[Description('Delete stored intake and webhook payloads older than the retention period')]
class PruneWebhookPayloads extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) Setting::get('retention.days', 90);

        $deleted = WebhookPayload::query()
            ->where('created_at', '<', Carbon::now()->subDays($days))
            ->delete();

        $this->info("Deleted {$deleted} payload record(s) older than {$days} day(s).");

        return self::SUCCESS;
    }
}
