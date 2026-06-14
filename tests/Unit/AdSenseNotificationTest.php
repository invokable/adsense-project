<?php

use App\Notifications\AdSenseNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('ads.metrics', [
        'PAGE_VIEWS',
        'ESTIMATED_EARNINGS',
        'INDIVIDUAL_AD_IMPRESSIONS',
        'ACTIVE_VIEW_VIEWABILITY',
    ]);
});

test('to mail generates correct email content', function () {
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

    expect($mailMessage)->toBeInstanceOf(MailMessage::class);
    $expectedDate = now()->format('Y/n/j');
    expect($mailMessage->subject)->toEqual("AdSense レポート（{$expectedDate}）");

    // Check that markdown template is being used
    expect($mailMessage->markdown)->toEqual('mail.ja.adsense-report');

    // Check that view data contains expected values
    $viewData = $mailMessage->viewData;
    expect($viewData)->toHaveKey('keyMetrics');
    expect($viewData)->toHaveKey('totalMetrics');
    expect($viewData)->toHaveKey('averageMetrics');
    expect($viewData)->toHaveKey('recentDays');

    // Check key metrics structure
    expect($viewData['keyMetrics'])->toHaveKey('today');
    expect($viewData['keyMetrics'])->toHaveKey('yesterday');
    expect($viewData['keyMetrics'])->toHaveKey('thisMonth');

    // Check total metrics
    expect($viewData['totalMetrics']['earnings'])->toEqual(125.0);
    expect($viewData['totalMetrics']['pageViews'])->toEqual(1000.0);
    expect($viewData['totalMetrics']['adImpressions'])->toEqual(3000.0);
    expect($viewData['totalMetrics']['viewability'])->toEqual(0.755);
});

test('to mail with english locale', function () {
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

    expect($mailMessage)->toBeInstanceOf(MailMessage::class);
    $expectedDate = now()->format('Y/n/j');
    expect($mailMessage->subject)->toEqual("AdSense Report ({$expectedDate})");
    expect($mailMessage->markdown)->toEqual('mail.en.adsense-report');

    // Check that keyMetrics is included in view data
    $viewData = $mailMessage->viewData;
    expect($viewData)->toHaveKey('keyMetrics');
});

test('via returns mail channel', function () {
    $notification = new AdSenseNotification([]);
    expect($notification->via((object) []))->toEqual(['mail']);
});
