<?php

use App\Notifications\AdSenseNotification;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:preview', function () {
    // Create sample report data for preview
    $reportData = [
        'totals' => [
            'cells' => [
                [],                     // Empty first cell
                ['value' => '5000'],    // PAGE_VIEWS
                ['value' => '150'],     // CLICKS
                ['value' => '2.8'],     // COST_PER_CLICK
                ['value' => '420.0'],   // ESTIMATED_EARNINGS
            ],
        ],
        'averages' => [
            'cells' => [
                [],                     // Empty first cell
                ['value' => '714'],     // PAGE_VIEWS
                ['value' => '21'],      // CLICKS
                ['value' => '2.8'],     // COST_PER_CLICK
                ['value' => '60.0'],    // ESTIMATED_EARNINGS
            ],
        ],
        'rows' => [
            [
                'cells' => [
                    ['value' => now()->format('Y-m-d')],  // Today
                    ['value' => '800'],
                    ['value' => '25'],
                    ['value' => '3.0'],
                    ['value' => '75.0'],
                ],
            ],
            [
                'cells' => [
                    ['value' => now()->subDay()->format('Y-m-d')],  // Yesterday
                    ['value' => '650'],
                    ['value' => '18'],
                    ['value' => '2.7'],
                    ['value' => '48.6'],
                ],
            ],
            [
                'cells' => [
                    ['value' => now()->subDays(2)->format('Y-m-d')],
                    ['value' => '720'],
                    ['value' => '22'],
                    ['value' => '2.9'],
                    ['value' => '63.8'],
                ],
            ],
        ],
    ];

    $notification = new AdSenseNotification($reportData);
    $mailMessage = $notification->toMail((object) []);

    // Render the email HTML
    $mailHtml = $mailMessage->render();

    // Create storage directory if it doesn't exist
    $storageDir = storage_path('framework/testing');
    if (! file_exists($storageDir)) {
        mkdir($storageDir, 0755, true);
    }

    // Save HTML files for both locales
    $jaFilePath = $storageDir.'/adsense-mail-preview-ja.html';
    $enFilePath = $storageDir.'/adsense-mail-preview-en.html';

    // Generate Japanese version
    config(['app.locale' => 'ja']);
    $jaMailMessage = $notification->toMail((object) []);
    $jaHtml = $jaMailMessage->render();
    file_put_contents($jaFilePath, $jaHtml);

    // Generate English version
    config(['app.locale' => 'en']);
    $enMailMessage = $notification->toMail((object) []);
    $enHtml = $enMailMessage->render();
    file_put_contents($enFilePath, $enHtml);

    $this->info('Email preview HTML files saved:');
    $this->line('Japanese: '.$jaFilePath);
    $this->line('English: '.$enFilePath);
    $this->line('');
    $this->info('Open these files in your browser to preview the email.');
})->purpose('Preview the AdSense notification email');
