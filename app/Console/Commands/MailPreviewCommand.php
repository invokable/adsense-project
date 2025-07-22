<?php

namespace App\Console\Commands;

use App\AdSenseReportTransformer;
use App\Notifications\AdSenseNotification;
use Illuminate\Console\Command;

class MailPreviewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:preview';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Preview the AdSense notification email';

    /**
     * Execute the console command.
     */
    public function handle(AdSenseReportTransformer $transformer): int
    {
        // Create sample report data for preview
        $rawReportData = [
            'totals' => [
                'cells' => [
                    [],                     // DATE dimension
                    [],                     // DOMAIN_CODE dimension
                    ['value' => '5000'],    // PAGE_VIEWS
                    ['value' => '420.0'],   // ESTIMATED_EARNINGS
                    ['value' => '15000'],   // INDIVIDUAL_AD_IMPRESSIONS
                    ['value' => '75.5'],    // ACTIVE_VIEW_VIEWABILITY
                ],
            ],
            'averages' => [
                'cells' => [
                    [],                     // DATE dimension
                    [],                     // DOMAIN_CODE dimension
                    ['value' => '714'],     // PAGE_VIEWS
                    ['value' => '60.0'],    // ESTIMATED_EARNINGS
                    ['value' => '2142'],    // INDIVIDUAL_AD_IMPRESSIONS
                    ['value' => '78.2'],    // ACTIVE_VIEW_VIEWABILITY
                ],
            ],
            'rows' => [
                [
                    'cells' => [
                        ['value' => now()->format('Y-m-d')],  // Today
                        ['value' => 'example.com'],
                        ['value' => '800'],
                        ['value' => '75.0'],
                        ['value' => '2400'],
                        ['value' => '82.1'],
                    ],
                ],
                [
                    'cells' => [
                        ['value' => now()->subDay()->format('Y-m-d')],  // Yesterday
                        ['value' => 'example.com'],
                        ['value' => '650'],
                        ['value' => '48.6'],
                        ['value' => '1950'],
                        ['value' => '79.3'],
                    ],
                ],
                [
                    'cells' => [
                        ['value' => now()->subDay()->format('Y-m-d')],  // Yesterday
                        ['value' => 'blog.example.com'],
                        ['value' => '420'],
                        ['value' => '31.2'],
                        ['value' => '1260'],
                        ['value' => '76.8'],
                    ],
                ],
                [
                    'cells' => [
                        ['value' => now()->subDays(2)->format('Y-m-d')],
                        ['value' => 'example.com'],
                        ['value' => '720'],
                        ['value' => '63.8'],
                        ['value' => '2160'],
                        ['value' => '80.5'],
                    ],
                ],
                [
                    'cells' => [
                        ['value' => now()->subDays(3)->format('Y-m-d')],
                        ['value' => 'example.com'],
                        ['value' => '580'],
                        ['value' => '42.0'],
                        ['value' => '1740'],
                        ['value' => '77.2'],
                    ],
                ],
                [
                    'cells' => [
                        ['value' => now()->subDays(4)->format('Y-m-d')],
                        ['value' => 'blog.example.com'],
                        ['value' => '690'],
                        ['value' => '65.1'],
                        ['value' => '2070'],
                        ['value' => '81.3'],
                    ],
                ],
                [
                    'cells' => [
                        ['value' => now()->subDays(5)->format('Y-m-d')],
                        ['value' => 'example.com'],
                        ['value' => '750'],
                        ['value' => '75.4'],
                        ['value' => '2250'],
                        ['value' => '83.7'],
                    ],
                ],
                [
                    'cells' => [
                        ['value' => now()->subDays(6)->format('Y-m-d')],
                        ['value' => 'example.com'],
                        ['value' => '620'],
                        ['value' => '49.4'],
                        ['value' => '1860'],
                        ['value' => '78.9'],
                    ],
                ],
                [
                    'cells' => [
                        ['value' => now()->subDays(7)->format('Y-m-d')],
                        ['value' => 'blog.example.com'],
                        ['value' => '710'],
                        ['value' => '69.0'],
                        ['value' => '2130'],
                        ['value' => '80.1'],
                    ],
                ],
                [
                    'cells' => [
                        ['value' => now()->subDays(8)->format('Y-m-d')],  // Yesterday week ago
                        ['value' => 'example.com'],
                        ['value' => '450'],
                        ['value' => '30.0'],  // This will show +18.6 (+62%) change
                        ['value' => '1350'],
                        ['value' => '74.5'],
                    ],
                ],
            ],
        ];

        // Transform raw data to notification data
        $notificationData = $transformer->toNotificationData($rawReportData);
        $notification = new AdSenseNotification($notificationData);

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

        return 0;
    }
}
