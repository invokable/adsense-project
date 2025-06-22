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
        //dd($this->reports);
        $endDate = $this->reports->endDate;
        $title = $endDate->year.'-'.$endDate->month.'-'.$endDate->day;
        $earnings = $this->reports->totals->cells[4]->value ?? 0;
        $page_views = $this->reports->totals->cells[1]->value ?? 0;
        $clicks = $this->reports->totals->cells[2]->value ?? 0;
        $cpc = $this->reports->totals->cells[3]->value ?? 0;
        info($earnings);
        info($page_views);
        info($clicks);
        info($cpc);

        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
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
