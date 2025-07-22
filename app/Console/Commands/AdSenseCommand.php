<?php

namespace App\Console\Commands;

use App\AdSenseReport;
use App\AdSenseReportTransformer;
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
    public function handle(AdSenseReport $adsense, AdSenseReportTransformer $transformer): int
    {
        $rawReports = $adsense->report();
        // dd($rawReports);
        $notificationData = $transformer->toNotificationData($rawReports);

        Notification::route('mail', [config('mail.to.address') => config('mail.to.name')])
            ->notify(new AdSenseNotification($notificationData));

        return 0;
    }
}
