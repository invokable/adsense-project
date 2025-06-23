<?php

namespace Tests\Unit;

use App\AdSenseReportTransformer;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AdSenseReportTransformerTest extends TestCase
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

    public function test_to_notification_data_transforms_raw_report_correctly(): void
    {
        $rawReports = [
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

        $transformer = new AdSenseReportTransformer;
        $result = $transformer->toNotificationData($rawReports);

        // Check structure
        $this->assertArrayHasKey('keyMetrics', $result);
        $this->assertArrayHasKey('yesterdayChange', $result);
        $this->assertArrayHasKey('totalMetrics', $result);
        $this->assertArrayHasKey('averageMetrics', $result);
        $this->assertArrayHasKey('recentDays', $result);
        $this->assertArrayHasKey('reportDate', $result);

        // Check total metrics
        $this->assertEquals(125.0, $result['totalMetrics']['earnings']);
        $this->assertEquals(1000.0, $result['totalMetrics']['pageViews']);
        $this->assertEquals(50.0, $result['totalMetrics']['clicks']);
        $this->assertEquals(2.5, $result['totalMetrics']['cpc']);

        // Check average metrics
        $this->assertEquals(17.9, $result['averageMetrics']['earnings']);
        $this->assertEquals(143.0, $result['averageMetrics']['pageViews']);
        $this->assertEquals(7.0, $result['averageMetrics']['clicks']);
        $this->assertEquals(2.5, $result['averageMetrics']['cpc']);

        // Check key metrics structure
        $this->assertArrayHasKey('today', $result['keyMetrics']);
        $this->assertArrayHasKey('yesterday', $result['keyMetrics']);
        $this->assertArrayHasKey('thisMonth', $result['keyMetrics']);
        $this->assertEquals(125.0, $result['keyMetrics']['thisMonth']);

        // Check recent days
        $this->assertCount(2, $result['recentDays']);
        $this->assertEquals('2023-12-01', $result['recentDays'][0]['date']);
        $this->assertEquals(20.0, $result['recentDays'][0]['earnings']);
        $this->assertEquals(150.0, $result['recentDays'][0]['pageViews']);

        // Check yesterdayChange structure
        $this->assertArrayHasKey('amount', $result['yesterdayChange']);
        $this->assertArrayHasKey('percentage', $result['yesterdayChange']);
        $this->assertArrayHasKey('direction', $result['yesterdayChange']);

        // Check reportDate is present
        $this->assertIsString($result['reportDate']);
    }

    public function test_to_notification_data_handles_empty_rows(): void
    {
        $rawReports = [
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
            'rows' => [],
        ];

        $transformer = new AdSenseReportTransformer;
        $result = $transformer->toNotificationData($rawReports);

        // Should still work with empty rows
        $this->assertIsArray($result['recentDays']);
        $this->assertEmpty($result['recentDays']);
        $this->assertEquals(0.0, $result['keyMetrics']['today']);
        $this->assertEquals(0.0, $result['keyMetrics']['yesterday']);
        $this->assertEquals(125.0, $result['keyMetrics']['thisMonth']);
    }

    public function test_to_notification_data_handles_missing_sections(): void
    {
        $rawReports = [
            'totals' => [
                'cells' => [
                    [],                   // Empty first cell
                    ['value' => '1000'],  // PAGE_VIEWS
                    ['value' => '50'],    // CLICKS
                    ['value' => '2.5'],   // COST_PER_CLICK
                    ['value' => '125.0'], // ESTIMATED_EARNINGS
                ],
            ],
            // Missing averages and rows
        ];

        $transformer = new AdSenseReportTransformer;
        $result = $transformer->toNotificationData($rawReports);

        // Should handle missing sections gracefully
        $this->assertEquals(0.0, $result['averageMetrics']['earnings']);
        $this->assertEmpty($result['recentDays']);
        $this->assertEquals(125.0, $result['totalMetrics']['earnings']);
    }
}
