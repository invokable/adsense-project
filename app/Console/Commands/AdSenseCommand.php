<?php

namespace App\Console\Commands;

use App\Notifications\AdSenseNotification;
use Google\Service\Adsense\Account;
use Google\Service\Adsense\ListAccountsResponse;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Revolution\Google\Client\Facades\Google;

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
    public function handle(): int
    {
        $token = [
            'access_token' => config('ads.access_token'),
            'refresh_token' => config('ads.refresh_token'),
            'expires_in' => 3600,
            'created' => now()->subDay()->getTimestamp(),
        ];

        Google::setAccessToken($token);

        Google::fetchAccessTokenWithRefreshToken();

        $ads = Google::make('Adsense');

        /** @var ListAccountsResponse $accounts */
        $accounts = $ads->accounts->listAccounts();

        /** @var Account $account */
        $account = head($accounts->getAccounts());

        $optParams = [
            'metrics' => config('ads.metrics'),
            'dimensions' => 'DATE',
            'orderBy' => '+DATE',
            'dateRange' => 'LAST_7_DAYS',
        ];

        $reports = $ads->accounts_reports
            ->generate($account->name, $optParams)
            ->toSimpleObject();

        Notification::route('mail', [config('mail.to.address') => config('mail.to.name')])
            ->notify(new AdSenseNotification($reports));

        return 0;
    }
}
