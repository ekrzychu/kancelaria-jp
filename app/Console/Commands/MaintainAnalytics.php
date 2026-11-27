<?php

namespace App\Console\Commands;

use App\Analytics\PageViews;
use Illuminate\Console\Command;

final class MaintainAnalytics extends Command
{
    protected $signature = 'analytics:maintain';

    protected $description = 'Record analytics coverage and prune expired daily counters';

    public function handle(): int
    {
        PageViews::maintain();

        return self::SUCCESS;
    }
}
