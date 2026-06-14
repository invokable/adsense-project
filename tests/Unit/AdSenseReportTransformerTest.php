<?php

use App\AdSenseReportTransformer;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('ads.metrics', [
        'PAGE_VIEWS',
        'ESTIMATED_EARNINGS',
        'INDIVIDUAL_AD_IMPRESSIONS',
        'ACTIVE_VIEW_VIEWABILITY',
    ]);
});

test('to notification data transforms raw report correctly', function () {
    $rawReports = [
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
            [
                'cells' => [
                    ['value' => '2023-12-02'],
                    ['value' => 'blog.example.com'],
                    ['value' => '200'],
                    ['value' => '30.0'],
                    ['value' => '600'],
                    ['value' => '0.802'], // ACTIVE_VIEW_VIEWABILITY (0-1 decimal)
                ],
            ],
        ],
    ];

    $transformer = new AdSenseReportTransformer;
    $result = $transformer->toNotificationData($rawReports);

    // Check structure
    expect($result)->toHaveKey('keyMetrics');
    expect($result)->toHaveKey('yesterdayChange');
    expect($result)->toHaveKey('totalMetrics');
    expect($result)->toHaveKey('averageMetrics');
    expect($result)->toHaveKey('recentDays');
    expect($result)->toHaveKey('domainBreakdown');
    expect($result)->toHaveKey('reportDate');

    // Check total metrics
    expect($result['totalMetrics']['earnings'])->toEqual(125.0);
    expect($result['totalMetrics']['pageViews'])->toEqual(1000.0);
    expect($result['totalMetrics']['adImpressions'])->toEqual(3000.0);
    expect($result['totalMetrics']['viewability'])->toEqual(75.5);

    // Check average metrics
    expect($result['averageMetrics']['earnings'])->toEqual(17.9);
    expect($result['averageMetrics']['pageViews'])->toEqual(143.0);
    expect($result['averageMetrics']['adImpressions'])->toEqual(428.0);
    expect($result['averageMetrics']['viewability'])->toEqual(76.2);

    // Check key metrics structure
    expect($result['keyMetrics'])->toHaveKey('today');
    expect($result['keyMetrics'])->toHaveKey('yesterday');
    expect($result['keyMetrics'])->toHaveKey('thisMonth');
    expect($result['keyMetrics']['thisMonth'])->toEqual(125.0);

    // Check recent days
    expect($result['recentDays'])->toHaveCount(2);
    expect($result['recentDays'][0]['date'])->toEqual('2023-12-01');
    expect($result['recentDays'][0]['domain'])->toEqual('example.com');
    expect($result['recentDays'][0]['earnings'])->toEqual(20.0);
    expect($result['recentDays'][0]['pageViews'])->toEqual(150.0);
    expect($result['recentDays'][0]['adImpressions'])->toEqual(450.0);
    expect($result['recentDays'][0]['viewability'])->toEqualWithDelta(78.1, 0.001);

    // Check domain breakdown
    expect($result)->toHaveKey('domainBreakdown');
    expect($result['domainBreakdown'])->toBeArray();

    // Check yesterdayChange structure
    expect($result['yesterdayChange'])->toHaveKey('amount');
    expect($result['yesterdayChange'])->toHaveKey('percentage');
    expect($result['yesterdayChange'])->toHaveKey('direction');

    // Check reportDate is present
    expect($result['reportDate'])->toBeString();
});

test('to notification data handles empty rows', function () {
    $rawReports = [
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
        'rows' => [],
    ];

    $transformer = new AdSenseReportTransformer;
    $result = $transformer->toNotificationData($rawReports);

    // Should still work with empty rows
    expect($result['recentDays'])->toBeArray();
    expect($result['recentDays'])->toBeEmpty();
    expect($result['keyMetrics']['today'])->toEqual(0.0);
    expect($result['keyMetrics']['yesterday'])->toEqual(0.0);
    expect($result['keyMetrics']['thisMonth'])->toEqual(125.0);
});

test('to notification data handles missing sections', function () {
    $rawReports = [
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
        // Missing averages and rows
    ];

    $transformer = new AdSenseReportTransformer;
    $result = $transformer->toNotificationData($rawReports);

    // Should handle missing sections gracefully
    expect($result['averageMetrics']['earnings'])->toEqual(0.0);
    expect($result['recentDays'])->toBeEmpty();
    expect($result['totalMetrics']['earnings'])->toEqual(125.0);
});
