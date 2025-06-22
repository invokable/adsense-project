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
            'CLICKS',
            'COST_PER_CLICK',
            'ESTIMATED_EARNINGS',
        ]);
    }

    public function test_to_mail_generates_correct_email_content(): void
    {
        $reportData = [
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
                [
                    'cells' => [
                        ['value' => '2023-12-02'],
                        ['value' => '200'],
                        ['value' => '10'],
                        ['value' => '3.0'],
                        ['value' => '30.0'],
                    ],
                ],
            ],
        ];

        $notification = new AdSenseNotification($reportData);
        $mailMessage = $notification->toMail((object) []);

        $this->assertInstanceOf(MailMessage::class, $mailMessage);
        $this->assertEquals('AdSense レポート（今月）', $mailMessage->subject);
        $this->assertEquals('AdSense レポート', $mailMessage->greeting);

        // Check basic structure
        $this->assertContains('今月のAdSenseレポートをお送りします。', $mailMessage->introLines);

        // Convert to string to check content
        $content = implode(' ', $mailMessage->introLines);
        $this->assertStringContainsString('収益: ¥125', $content);
        $this->assertStringContainsString('ページビュー: 1,000', $content);
        $this->assertStringContainsString('クリック数: 50', $content);
    }

    public function test_get_metric_value_returns_correct_values(): void
    {
        $reportData = [
            'totals' => [
                'cells' => [
                    [],                   // Empty first cell
                    ['value' => '1000'],  // PAGE_VIEWS
                    ['value' => '50'],    // CLICKS
                    ['value' => '2.5'],   // COST_PER_CLICK
                    ['value' => '125.0'], // ESTIMATED_EARNINGS
                ],
            ],
        ];

        $notification = new AdSenseNotification($reportData);

        // Use reflection to access private method
        $reflection = new \ReflectionClass($notification);
        $method = $reflection->getMethod('getMetricValue');
        $method->setAccessible(true);

        $this->assertEquals(1000.0, $method->invoke($notification, 'PAGE_VIEWS'));
        $this->assertEquals(50.0, $method->invoke($notification, 'CLICKS'));
        $this->assertEquals(2.5, $method->invoke($notification, 'COST_PER_CLICK'));
        $this->assertEquals(125.0, $method->invoke($notification, 'ESTIMATED_EARNINGS'));
        $this->assertEquals(0.0, $method->invoke($notification, 'INVALID_METRIC'));
    }

    public function test_get_metric_value_with_custom_data_source(): void
    {
        $reportData = [
            'totals' => [
                'cells' => [
                    ['value' => '1000'],
                    ['value' => '50'],
                    ['value' => '2.5'],
                    ['value' => '125.0'],
                ],
            ],
        ];

        $customDataSource = [
            'cells' => [
                [],                   // Empty first cell
                ['value' => '500'],   // PAGE_VIEWS
                ['value' => '25'],    // CLICKS
                ['value' => '3.0'],   // COST_PER_CLICK
                ['value' => '75.0'],  // ESTIMATED_EARNINGS
            ],
        ];

        $notification = new AdSenseNotification($reportData);

        $reflection = new \ReflectionClass($notification);
        $method = $reflection->getMethod('getMetricValue');
        $method->setAccessible(true);

        $this->assertEquals(500.0, $method->invoke($notification, 'PAGE_VIEWS', $customDataSource));
        $this->assertEquals(25.0, $method->invoke($notification, 'CLICKS', $customDataSource));
    }

    public function test_via_returns_mail_channel(): void
    {
        $notification = new AdSenseNotification([]);
        $this->assertEquals(['mail'], $notification->via((object) []));
    }
}
