<?php

namespace Tests\Feature;

use App\AdSenseReport;
use App\AdSenseReportTransformer;
use App\Console\Commands\AdSenseCommand;
use App\Notifications\AdSenseNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdSenseCommandTest extends TestCase
{
    public function test_handle_sends_adsense_report(): void
    {
        // Config setup
        Config::set('mail.to.address', 'test@example.com');
        Config::set('mail.to.name', 'Test User');

        // Mock AdSenseReport
        $mockReport = $this->createMock(AdSenseReport::class);
        $mockReport->expects($this->once())
            ->method('report')
            ->willReturn([
                'totals' => [
                    'cells' => [
                        [],                   // Empty first cell
                        ['value' => '1000'],  // PAGE_VIEWS
                        ['value' => '50'],    // CLICKS
                        ['value' => '2.5'],   // COST_PER_CLICK
                        ['value' => '125.0'], // ESTIMATED_EARNINGS
                    ],
                ],
                'averages' => [
                    'cells' => [
                        [],                   // Empty first cell
                        ['value' => '143'],   // PAGE_VIEWS
                        ['value' => '7'],     // CLICKS
                        ['value' => '2.5'],   // COST_PER_CLICK
                        ['value' => '17.9'],  // ESTIMATED_EARNINGS
                    ],
                ],
                'rows' => [
                    [
                        'cells' => [
                            ['value' => '2023-12-01'],
                            ['value' => '150'],
                            ['value' => '8'],
                            ['value' => '2.5'],
                            ['value' => '20.0'],
                        ],
                    ],
                ],
            ]);

        // Mock AdSenseReportTransformer
        $mockTransformer = $this->createMock(AdSenseReportTransformer::class);
        $mockTransformer->expects($this->once())
            ->method('toNotificationData')
            ->willReturn([
                'keyMetrics' => [
                    'today' => 0.0,
                    'yesterday' => 0.0,
                    'thisMonth' => 125.0,
                ],
                'yesterdayChange' => [
                    'amount' => 0.0,
                    'percentage' => 0,
                    'direction' => 'neutral',
                ],
                'totalMetrics' => [
                    'earnings' => 125.0,
                    'pageViews' => 1000.0,
                    'clicks' => 50.0,
                    'cpc' => 2.5,
                ],
                'averageMetrics' => [
                    'earnings' => 17.9,
                    'pageViews' => 143.0,
                    'clicks' => 7.0,
                    'cpc' => 2.5,
                ],
                'recentDays' => [],
                'reportDate' => '2023-12-03 12:00:00',
            ]);

        $this->app->instance(AdSenseReport::class, $mockReport);
        $this->app->instance(AdSenseReportTransformer::class, $mockTransformer);

        // Mock Notification
        Notification::fake();

        // Execute command
        $this->artisan('ads:report')
            ->assertExitCode(0);

        // Assert notification was sent
        Notification::assertSentOnDemand(AdSenseNotification::class);
    }
}
