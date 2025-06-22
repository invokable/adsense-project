<?php

namespace App\Console\Commands;

use App\AdSenseReport;
use App\Notifications\AdSenseNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class AdSenseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ads:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(AdSenseReport $adsense): int
    {
        $reports = $adsense->report();

        Notification::route('mail', [config('mail.to.address') => config('mail.to.name')])
            ->notify(new AdSenseNotification($reports));

        return 0;
    }
}
