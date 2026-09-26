<?php

namespace App\Notifications;

use App\Models\Loan;
use App\Notifications\Concerns\SendsLibraryNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanReturnedNotification extends Notification implements ShouldQueue
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
            'title' => 'Pengembalian berhasil',
            'message' => "Peminjaman {$this->loan->code} telah selesai dikembalikan.",
            'icon' => 'ni ni-check-bold',
            'url' => route('loans.show', $this->loan),
            'color' => 'from-emerald-500 to-teal-400',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pengembalian Berhasil — '.$this->loan->code)
            ->line("Peminjaman {$this->loan->code} telah kami terima kembali. Terima kasih!")
            ->action('Lihat Riwayat', route('loans.show', $this->loan));
    }
}
