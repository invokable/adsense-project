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
                        [],                   // DATE dimension
                        [],                   // DOMAIN_CODE dimension
                        ['value' => '1000'],  // PAGE_VIEWS
                        ['value' => '125.0'], // ESTIMATED_EARNINGS
                        ['value' => '3000'],  // INDIVIDUAL_AD_IMPRESSIONS
                        ['value' => '0.755'], // ACTIVE_VIEW_VIEWABILITY (0-1 decimal)
                    ],
                ],
                'averages' => [
                    'cells' => [
                        [],                   // DATE dimension
                        [],                   // DOMAIN_CODE dimension
                        ['value' => '143'],   // PAGE_VIEWS
                        ['value' => '17.9'],  // ESTIMATED_EARNINGS
                        ['value' => '428'],   // INDIVIDUAL_AD_IMPRESSIONS
                        ['value' => '0.762'], // ACTIVE_VIEW_VIEWABILITY (0-1 decimal)
                    ],
                ],
                'rows' => [
                    [
                        'cells' => [
                            ['value' => '2023-12-01'],
                            ['value' => 'example.com'],
                            ['value' => '150'],
                            ['value' => '20.0'],
                            ['value' => '450'],
                            ['value' => '0.781'], // ACTIVE_VIEW_VIEWABILITY (0-1 decimal)
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
                    'adImpressions' => 3000.0,
                    'viewability' => 0.755,
                ],
                'averageMetrics' => [
                    'earnings' => 17.9,
                    'pageViews' => 143.0,
                    'adImpressions' => 428.0,
                    'viewability' => 0.762,
                ],
                'recentDays' => [],
                'domainBreakdown' => [],
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
