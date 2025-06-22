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
    public function __construct(protected object $reports)
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
        $totalEarnings = $this->getMetricValue('ESTIMATED_EARNINGS');
        $totalPageViews = $this->getMetricValue('PAGE_VIEWS');
        $totalClicks = $this->getMetricValue('CLICKS');
        $totalCpc = $this->getMetricValue('COST_PER_CLICK');

        $avgEarnings = $this->getMetricValue('ESTIMATED_EARNINGS', $this->reports->averages);
        $avgPageViews = $this->getMetricValue('PAGE_VIEWS', $this->reports->averages);
        $avgClicks = $this->getMetricValue('CLICKS', $this->reports->averages);
        $avgCpc = $this->getMetricValue('COST_PER_CLICK', $this->reports->averages);

        $mailMessage = (new MailMessage)
            ->subject('AdSense レポート（7日間）')
            ->greeting('AdSense レポート')
            ->line('過去7日間のAdSenseレポートをお送りします。')
            ->line('')
            ->line('**合計実績**')
            ->line('収益: ¥'.number_format($totalEarnings))
            ->line('ページビュー: '.number_format($totalPageViews))
            ->line('クリック数: '.number_format($totalClicks))
            ->line('CPC: ¥'.number_format($totalCpc))
            ->line('')
            ->line('**日平均実績**')
            ->line('収益: ¥'.number_format($avgEarnings))
            ->line('ページビュー: '.number_format($avgPageViews))
            ->line('クリック数: '.number_format($avgClicks))
            ->line('CPC: ¥'.number_format($avgCpc))
            ->line('');

        if (isset($this->reports->rows) && count($this->reports->rows) > 0) {
            $mailMessage->line('**日別詳細**');
            foreach ($this->reports->rows as $row) {
                $date = $row->cells[0]->value ?? 'N/A';
                $pageViews = $this->getMetricValue('PAGE_VIEWS', $row);
                $clicks = $this->getMetricValue('CLICKS', $row);
                $cpc = $this->getMetricValue('COST_PER_CLICK', $row);
                $earnings = $this->getMetricValue('ESTIMATED_EARNINGS', $row);

                $mailMessage->line("📅 {$date}");
                $mailMessage->line('　収益: ¥'.number_format($earnings).' | ページビュー: '.number_format($pageViews).' | クリック数: '.number_format($clicks).' | CPC: ¥'.number_format($cpc));
            }
        }

        return $mailMessage->line('')->line('レポート作成日時: '.now()->format('Y-m-d H:i:s'));
    }

    /**
     * Get metric value by name from data source
     */
    private function getMetricValue(string $metricName, object $dataSource = null): float
    {
        $metrics = config('ads.metrics');
        $index = array_search($metricName, $metrics);
        
        if ($index === false) {
            return 0;
        }
        
        $dataSource = $dataSource ?? $this->reports->totals;
        return $dataSource->cells[$index]->value ?? 0;
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
