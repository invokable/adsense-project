<?php

namespace Tests\Unit;

use App\AdSenseReport;
use Google\Service\Adsense;
use Google\Service\Adsense\Account;
use Google\Service\Adsense\AccountsResource;
use Google\Service\Adsense\ListAccountsResponse;
use Google\Service\Adsense\ReportsResource;
use Illuminate\Support\Facades\Config;
use Mockery;
use Revolution\Google\Client\Facades\Google;
use Tests\TestCase;

class AdSenseReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('ads.access_token', 'test_access_token');
        Config::set('ads.refresh_token', 'test_refresh_token');
        Config::set('ads.metrics', [
            'PAGE_VIEWS',
            'ESTIMATED_EARNINGS',
            'INDIVIDUAL_AD_IMPRESSIONS',
            'ACTIVE_VIEW_VIEWABILITY',
        ]);
    }

    public function test_report_returns_array_data(): void
    {
        // Mock Google Facade
        Google::shouldReceive('setAccessToken')
            ->once()
            ->with(Mockery::on(function ($token) {
                return $token['access_token'] === 'test_access_token'
                    && $token['refresh_token'] === 'test_refresh_token';
            }));

        Google::shouldReceive('fetchAccessTokenWithRefreshToken')->once();

        // Mock AdSense service
        $mockAdsense = Mockery::mock(Adsense::class);
        Google::shouldReceive('make')
            ->with('Adsense')
            ->once()
            ->andReturn($mockAdsense);

        // Mock accounts
        $mockAccount = new Account;
        $mockAccount->name = 'accounts/pub-1234567890';

        $mockAccountsResponse = new ListAccountsResponse;
        $mockAccountsResponse->setAccounts([$mockAccount]);

        $mockAccountsResource = Mockery::mock(AccountsResource::class);
        $mockAccountsResource->shouldReceive('listAccounts')
            ->once()
            ->andReturn($mockAccountsResponse);

        $mockAdsense->accounts = $mockAccountsResource;

        // Mock reports
        $mockReportData = (object) [
            'totals' => (object) [
                'cells' => [
                    (object) [],                    // DATE dimension
                    (object) [],                    // DOMAIN_CODE dimension
                    (object) ['value' => '1000'],   // PAGE_VIEWS
                    (object) ['value' => '125.0'],  // ESTIMATED_EARNINGS
                    (object) ['value' => '3000'],   // INDIVIDUAL_AD_IMPRESSIONS
                    (object) ['value' => '0.755'],  // ACTIVE_VIEW_VIEWABILITY (0-1 decimal)
                ],
            ],
            'averages' => (object) [
                'cells' => [
                    (object) [],                    // DATE dimension
                    (object) [],                    // DOMAIN_CODE dimension
                    (object) ['value' => '143'],    // PAGE_VIEWS
                    (object) ['value' => '17.9'],   // ESTIMATED_EARNINGS
                    (object) ['value' => '428'],    // INDIVIDUAL_AD_IMPRESSIONS
                    (object) ['value' => '0.762'],  // ACTIVE_VIEW_VIEWABILITY (0-1 decimal)
                ],
            ],
            'rows' => [
                (object) [
                    'cells' => [
                        (object) ['value' => '2023-12-01'],
                        (object) ['value' => 'example.com'],
                        (object) ['value' => '150'],
                        (object) ['value' => '20.0'],
                        (object) ['value' => '450'],
                        (object) ['value' => '0.781'], // ACTIVE_VIEW_VIEWABILITY (0-1 decimal)
                    ],
                ],
            ],
        ];

        $mockReportResponse = Mockery::mock();
        $mockReportResponse->shouldReceive('toSimpleObject')
            ->once()
            ->andReturn($mockReportData);

        $mockReportsResource = Mockery::mock(ReportsResource::class);
        $mockReportsResource->shouldReceive('generate')
            ->once()
            ->with(
                'accounts/pub-1234567890',
                Mockery::on(function ($params) {
                    return $params['metrics'] === config('ads.metrics')
                        && $params['dimensions'] === ['DATE', 'DOMAIN_CODE']
                        && $params['orderBy'] === '-DATE'
                        && $params['dateRange'] === 'MONTH_TO_DATE';
                })
            )
            ->andReturn($mockReportResponse);

        $mockAdsense->accounts_reports = $mockReportsResource;

        // Execute
        $adsenseReport = new AdSenseReport;
        $result = $adsenseReport->report();

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('totals', $result);
        $this->assertArrayHasKey('averages', $result);
        $this->assertArrayHasKey('rows', $result);

        // Check data structure
        $this->assertEquals('1000', $result['totals']['cells'][2]['value']);
        $this->assertEquals('125.0', $result['totals']['cells'][3]['value']);
        $this->assertEquals('2023-12-01', $result['rows'][0]['cells'][0]['value']);
    }

    public function test_report_uses_correct_config_values(): void
    {
        // Override config for this test
        Config::set('ads.metrics', ['PAGE_VIEWS', 'ESTIMATED_EARNINGS']);

        Google::shouldReceive('setAccessToken')->once();
        Google::shouldReceive('fetchAccessTokenWithRefreshToken')->once();

        $mockAdsense = Mockery::mock(Adsense::class);
        Google::shouldReceive('make')->andReturn($mockAdsense);

        $mockAccount = new Account;
        $mockAccount->name = 'accounts/test';

        $mockAccountsResponse = new ListAccountsResponse;
        $mockAccountsResponse->setAccounts([$mockAccount]);

        $mockAccountsResource = Mockery::mock(AccountsResource::class);
        $mockAccountsResource->shouldReceive('listAccounts')->andReturn($mockAccountsResponse);
        $mockAdsense->accounts = $mockAccountsResource;

        $mockReportResponse = Mockery::mock();
        $mockReportResponse->shouldReceive('toSimpleObject')->andReturn((object) []);

        $mockReportsResource = Mockery::mock(ReportsResource::class);
        $mockReportsResource->shouldReceive('generate')
            ->with(
                'accounts/test',
                Mockery::on(function ($params) {
                    return $params['metrics'] === ['PAGE_VIEWS', 'ESTIMATED_EARNINGS'];
                })
            )
            ->andReturn($mockReportResponse);

        $mockAdsense->accounts_reports = $mockReportsResource;

        $adsenseReport = new AdSenseReport;
        $adsenseReport->report();
    }
}
