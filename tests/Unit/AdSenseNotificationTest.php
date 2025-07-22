<?php

namespace Tests\Unit;

use App\Notifications\AdSenseNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AdSenseNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('ads.metrics', [
            'PAGE_VIEWS',
            'ESTIMATED_EARNINGS',
            'INDIVIDUAL_AD_IMPRESSIONS',
            'ACTIVE_VIEW_VIEWABILITY',
        ]);
    }

    public function test_to_mail_generates_correct_email_content(): void
    {
        // Set Japanese locale for this test
        Config::set('app.locale', 'ja');

        $notificationData = [
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
            'recentDays' => [
                [
                    'date' => '2023-12-01',
                    'domain' => 'example.com',
                    'earnings' => 20.0,
                    'pageViews' => 150.0,
                    'adImpressions' => 450.0,
                    'viewability' => 0.781,
                ],
                [
                    'date' => '2023-12-02',
                    'domain' => 'blog.example.com',
                    'earnings' => 30.0,
                    'pageViews' => 200.0,
                    'adImpressions' => 600.0,
                    'viewability' => 0.802,
                ],
            ],
            'domainBreakdown' => [],
            'reportDate' => '2023-12-03 12:00:00',
        ];

        $notification = new AdSenseNotification($notificationData);
        $mailMessage = $notification->toMail((object) []);

        $this->assertInstanceOf(MailMessage::class, $mailMessage);
        $expectedDate = now()->format('Y/n/j');
        $this->assertEquals("AdSense レポート（{$expectedDate}）", $mailMessage->subject);

        // Check that markdown template is being used
        $this->assertEquals('mail.ja.adsense-report', $mailMessage->markdown);

        // Check that view data contains expected values
        $viewData = $mailMessage->viewData;
        $this->assertArrayHasKey('keyMetrics', $viewData);
        $this->assertArrayHasKey('totalMetrics', $viewData);
        $this->assertArrayHasKey('averageMetrics', $viewData);
        $this->assertArrayHasKey('recentDays', $viewData);

        // Check key metrics structure
        $this->assertArrayHasKey('today', $viewData['keyMetrics']);
        $this->assertArrayHasKey('yesterday', $viewData['keyMetrics']);
        $this->assertArrayHasKey('thisMonth', $viewData['keyMetrics']);

        // Check total metrics
        $this->assertEquals(125.0, $viewData['totalMetrics']['earnings']);
        $this->assertEquals(1000.0, $viewData['totalMetrics']['pageViews']);
        $this->assertEquals(3000.0, $viewData['totalMetrics']['adImpressions']);
        $this->assertEquals(0.755, $viewData['totalMetrics']['viewability']);
    }

    public function test_to_mail_with_english_locale(): void
    {
        // Set English locale for this test
        Config::set('app.locale', 'en');

        $notificationData = [
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
                'viewability' => 75.5,
            ],
            'averageMetrics' => [
                'earnings' => 17.9,
                'pageViews' => 143.0,
                'adImpressions' => 428.0,
                'viewability' => 76.2,
            ],
            'recentDays' => [],
            'domainBreakdown' => [],
            'reportDate' => '2023-12-03 12:00:00',
        ];

        $notification = new AdSenseNotification($notificationData);
        $mailMessage = $notification->toMail((object) []);

        $this->assertInstanceOf(MailMessage::class, $mailMessage);
        $expectedDate = now()->format('Y/n/j');
        $this->assertEquals("AdSense Report ({$expectedDate})", $mailMessage->subject);
        $this->assertEquals('mail.en.adsense-report', $mailMessage->markdown);

        // Check that keyMetrics is included in view data
        $viewData = $mailMessage->viewData;
        $this->assertArrayHasKey('keyMetrics', $viewData);
    }

    public function test_via_returns_mail_channel(): void
    {
        $notification = new AdSenseNotification([]);
        $this->assertEquals(['mail'], $notification->via((object) []));
    }
}
