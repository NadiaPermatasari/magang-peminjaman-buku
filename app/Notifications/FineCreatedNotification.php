<?php

namespace App\Notifications;

use App\Models\Fine;
use App\Notifications\Concerns\SendsLibraryNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FineCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable, SendsLibraryNotifications;

    public function __construct(public Fine $fine) {}

    public function via(object $notifiable): array
    {
        return $this->libraryChannels();
    }

    public function toDatabase(object $notifiable): array
    {
        $amount = number_format($this->fine->amount, 0, ',', '.');

        return [
            'title' => 'Denda keterlambatan',
            'message' => "Anda dikenakan denda Rp{$amount} untuk keterlambatan {$this->fine->late_days} hari.",
            'icon' => 'ni ni-money-coins',
            'url' => route('fines.index'),
            'color' => 'from-orange-500 to-yellow-500',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->fine->amount, 0, ',', '.');

        return (new MailMessage)
            ->subject('Denda Keterlambatan')
            ->line("Anda dikenakan denda sebesar Rp{$amount} untuk keterlambatan {$this->fine->late_days} hari.")
            ->action('Lihat Denda', route('fines.index'));
    }
}
