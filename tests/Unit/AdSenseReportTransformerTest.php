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
            'ESTIMATED_EARNINGS',
            'INDIVIDUAL_AD_IMPRESSIONS',
            'ACTIVE_VIEW_VIEWABILITY',
        ]);
    }

    public function test_to_notification_data_transforms_raw_report_correctly(): void
    {
        $rawReports = [
            'totals' => [
                'cells' => [
                    [],                   // DATE dimension
                    [],                   // DOMAIN_CODE dimension
                    ['value' => '1000'],  // PAGE_VIEWS
                    ['value' => '125.0'], // ESTIMATED_EARNINGS
                    ['value' => '3000'],  // INDIVIDUAL_AD_IMPRESSIONS
                    ['value' => '75.5'],  // ACTIVE_VIEW_VIEWABILITY
                ],
            ],
            'averages' => [
                'cells' => [
                    [],                   // DATE dimension
                    [],                   // DOMAIN_CODE dimension
                    ['value' => '143'],   // PAGE_VIEWS
                    ['value' => '17.9'],  // ESTIMATED_EARNINGS
                    ['value' => '428'],   // INDIVIDUAL_AD_IMPRESSIONS
                    ['value' => '76.2'],  // ACTIVE_VIEW_VIEWABILITY
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
                        ['value' => '78.1'],
                    ],
                ],
                [
                    'cells' => [
                        ['value' => '2023-12-02'],
                        ['value' => 'blog.example.com'],
                        ['value' => '200'],
                        ['value' => '30.0'],
                        ['value' => '600'],
                        ['value' => '80.2'],
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
        $this->assertArrayHasKey('domainBreakdown', $result);
        $this->assertArrayHasKey('reportDate', $result);

        // Check total metrics
        $this->assertEquals(125.0, $result['totalMetrics']['earnings']);
        $this->assertEquals(1000.0, $result['totalMetrics']['pageViews']);
        $this->assertEquals(3000.0, $result['totalMetrics']['adImpressions']);
        $this->assertEquals(75.5, $result['totalMetrics']['viewability']);

        // Check average metrics
        $this->assertEquals(17.9, $result['averageMetrics']['earnings']);
        $this->assertEquals(143.0, $result['averageMetrics']['pageViews']);
        $this->assertEquals(428.0, $result['averageMetrics']['adImpressions']);
        $this->assertEquals(76.2, $result['averageMetrics']['viewability']);

        // Check key metrics structure
        $this->assertArrayHasKey('today', $result['keyMetrics']);
        $this->assertArrayHasKey('yesterday', $result['keyMetrics']);
        $this->assertArrayHasKey('thisMonth', $result['keyMetrics']);
        $this->assertEquals(125.0, $result['keyMetrics']['thisMonth']);

        // Check recent days
        $this->assertCount(2, $result['recentDays']);
        $this->assertEquals('2023-12-01', $result['recentDays'][0]['date']);
        $this->assertEquals('example.com', $result['recentDays'][0]['domain']);
        $this->assertEquals(20.0, $result['recentDays'][0]['earnings']);
        $this->assertEquals(150.0, $result['recentDays'][0]['pageViews']);
        $this->assertEquals(450.0, $result['recentDays'][0]['adImpressions']);
        $this->assertEquals(78.1, $result['recentDays'][0]['viewability']);

        // Check domain breakdown
        $this->assertArrayHasKey('domainBreakdown', $result);
        $this->assertIsArray($result['domainBreakdown']);

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
                    [],                   // DATE dimension
                    [],                   // DOMAIN_CODE dimension
                    ['value' => '1000'],  // PAGE_VIEWS
                    ['value' => '125.0'], // ESTIMATED_EARNINGS
                    ['value' => '3000'],  // INDIVIDUAL_AD_IMPRESSIONS
                    ['value' => '75.5'],  // ACTIVE_VIEW_VIEWABILITY
                ],
            ],
            'averages' => [
                'cells' => [
                    [],                   // DATE dimension
                    [],                   // DOMAIN_CODE dimension
                    ['value' => '143'],   // PAGE_VIEWS
                    ['value' => '17.9'],  // ESTIMATED_EARNINGS
                    ['value' => '428'],   // INDIVIDUAL_AD_IMPRESSIONS
                    ['value' => '76.2'],  // ACTIVE_VIEW_VIEWABILITY
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
                    [],                   // DATE dimension
                    [],                   // DOMAIN_CODE dimension
                    ['value' => '1000'],  // PAGE_VIEWS
                    ['value' => '125.0'], // ESTIMATED_EARNINGS
                    ['value' => '3000'],  // INDIVIDUAL_AD_IMPRESSIONS
                    ['value' => '75.5'],  // ACTIVE_VIEW_VIEWABILITY
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
