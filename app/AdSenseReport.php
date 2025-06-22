<?php

namespace App;

use Google\Service\Adsense;
use Google\Service\Adsense\Account;
use Revolution\Google\Client\Facades\Google;

class AdSenseReport
{
    public function report(): array
    {
        $token = [
            'access_token' => config('ads.access_token'),
            'refresh_token' => config('ads.refresh_token'),
            'expires_in' => 3600,
            'created' => now()->subDay()->getTimestamp(),
        ];

        Google::setAccessToken($token);

        Google::fetchAccessTokenWithRefreshToken();

        /** @var Adsense $ads */
        $ads = Google::make('Adsense');

        $accounts = $ads->accounts->listAccounts();

        /** @var Account $account */
        $account = head($accounts->getAccounts());

        $optParams = [
            'metrics' => config('ads.metrics'),
            'dimensions' => 'DATE',
            'orderBy' => '-DATE',
            'dateRange' => 'MONTH_TO_DATE',
        ];

        $reports = $ads->accounts_reports
            ->generate($account->name, $optParams)
            ->toSimpleObject();

        return json_decode(json_encode($reports), true);
    }
}
