<?php

namespace App\Notifications;

use App\Models\Loan;
use App\Notifications\Concerns\SendsLibraryNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable, SendsLibraryNotifications;

    public function __construct(public Loan $loan) {}

    public function via(object $notifiable): array
    {
        return $this->libraryChannels();
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Pengajuan peminjaman terkirim',
            'message' => "Pengajuan {$this->loan->code} sedang menunggu verifikasi petugas.",
            'icon' => 'ni ni-cart',
            'url' => route('loans.show', $this->loan),
            'color' => 'from-blue-500 to-violet-500',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pengajuan Peminjaman Terkirim — '.$this->loan->code)
            ->line("Pengajuan peminjaman dengan kode {$this->loan->code} telah kami terima.")
            ->line('Pengajuan Anda sedang menunggu verifikasi petugas.')
            ->action('Lihat Status Pengajuan', route('loans.show', $this->loan));
    }
}
