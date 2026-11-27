<?php

namespace App\Console\Commands;

use App\Models\ContactMessage;
use Illuminate\Console\Command;

final class ReportContactRetention extends Command
{
    protected $signature = 'contact:retention';

    protected $description = 'Count archived messages past the configured retention period';

    public function handle(): int
    {
        $days = config('mail.contact.retention_days');
        if ($days < 1) {
            $this->info('Contact retention reporting is disabled.');

            return self::SUCCESS;
        }

        $count = ContactMessage::where('status', 'archived')->where('archived_at', '<=', now()->subDays($days))->count();
        $this->info("Archived contact messages older than {$days} day(s): {$count}.");

        return self::SUCCESS;
    }
}
