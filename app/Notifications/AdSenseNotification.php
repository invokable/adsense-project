<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdSenseNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected array $reports)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $locale = config('app.locale', 'en');
        $template = $locale === 'ja' ? 'mail.ja.adsense-report' : 'mail.en.adsense-report';
        $subject = $locale === 'ja' ? 'AdSense レポート（今月）' : 'AdSense Report (This Month)';

        $totalMetrics = [
            'earnings' => $this->getMetricValue('ESTIMATED_EARNINGS'),
            'pageViews' => $this->getMetricValue('PAGE_VIEWS'),
            'clicks' => $this->getMetricValue('CLICKS'),
            'cpc' => $this->getMetricValue('COST_PER_CLICK'),
        ];

        $averageMetrics = [
            'earnings' => $this->getMetricValue('ESTIMATED_EARNINGS', $this->reports['averages']),
            'pageViews' => $this->getMetricValue('PAGE_VIEWS', $this->reports['averages']),
            'clicks' => $this->getMetricValue('CLICKS', $this->reports['averages']),
            'cpc' => $this->getMetricValue('COST_PER_CLICK', $this->reports['averages']),
        ];

        $recentDays = [];
        if (isset($this->reports['rows']) && count($this->reports['rows']) > 0) {
            $recentRows = array_slice($this->reports['rows'], 0, 7);
            foreach ($recentRows as $row) {
                $recentDays[] = [
                    'date' => $row['cells'][0]['value'] ?? 'N/A',
                    'earnings' => $this->getMetricValue('ESTIMATED_EARNINGS', $row),
                    'pageViews' => $this->getMetricValue('PAGE_VIEWS', $row),
                    'clicks' => $this->getMetricValue('CLICKS', $row),
                    'cpc' => $this->getMetricValue('COST_PER_CLICK', $row),
                ];
            }
        }

        return (new MailMessage)
            ->subject($subject)
            ->markdown($template, [
                'totalMetrics' => $totalMetrics,
                'averageMetrics' => $averageMetrics,
                'recentDays' => $recentDays,
                'reportDate' => now()->format('Y-m-d H:i:s'),
            ]);
    }

    /**
     * Get metric value by name from data source
     */
    private function getMetricValue(string $metricName, ?array $dataSource = null): float
    {
        $metrics = config('ads.metrics');
        $index = array_search($metricName, $metrics);

        if ($index === false) {
            return 0;
        }

        $dataSource = $dataSource ?? $this->reports['totals'];
        $value = $dataSource['cells'][$index + 1]['value'] ?? 0;

        return (float) $value;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
