<?php

namespace App\Notifications;

use App\Models\LoanExtension;
use App\Notifications\Concerns\SendsLibraryNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanExtensionSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable, SendsLibraryNotifications;

    public function __construct(public LoanExtension $extension) {}

    public function via(object $notifiable): array
    {
        return $this->libraryChannels();
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Pengajuan perpanjangan terkirim',
            'message' => "Permintaan perpanjangan {$this->extension->days} hari untuk peminjaman {$this->extension->loan->code} sedang menunggu persetujuan petugas.",
            'icon' => 'ni ni-calendar-grid-58',
            'url' => route('loans.show', $this->extension->loan),
            'color' => 'from-orange-500 to-yellow-500',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pengajuan Perpanjangan Terkirim — '.$this->extension->loan->code)
            ->line("Permintaan perpanjangan {$this->extension->days} hari untuk peminjaman {$this->extension->loan->code} telah kami terima.")
            ->line('Anda akan diberi tahu begitu petugas menyetujui atau menolaknya.')
            ->action('Lihat Detail', route('loans.show', $this->extension->loan));
    }
}
