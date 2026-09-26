<?php

namespace App\Notifications;

use App\Models\Fine;
use App\Notifications\Concerns\SendsLibraryNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FinePaidNotification extends Notification implements ShouldQueue
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
            'title' => 'Denda telah dibayar',
            'message' => "Denda sebesar Rp{$amount} telah dinyatakan lunas. Terima kasih!",
            'icon' => 'ni ni-money-coins',
            'url' => route('fines.index'),
            'color' => 'from-emerald-500 to-teal-400',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->fine->amount, 0, ',', '.');

        return (new MailMessage)
            ->subject('Denda Telah Dibayar')
            ->line("Denda sebesar Rp{$amount} telah dinyatakan lunas. Terima kasih!")
            ->action('Lihat Riwayat', route('fines.index'));
    }
}
