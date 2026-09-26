<?php

namespace App\Notifications;

use App\Models\Loan;
use App\Notifications\Concerns\SendsLibraryNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable, SendsLibraryNotifications;

    public function __construct(public Loan $loan, public int $lateDays) {}

    public function via(object $notifiable): array
    {
        return $this->libraryChannels();
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Peminjaman terlambat',
            'message' => "Peminjaman {$this->loan->code} telah terlambat {$this->lateDays} hari. Segera kembalikan buku.",
            'icon' => 'ni ni-time-alarm',
            'url' => route('loans.show', $this->loan),
            'color' => 'from-red-600 to-orange-600',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Peminjaman Terlambat — '.$this->loan->code)
            ->line("Peminjaman {$this->loan->code} telah terlambat {$this->lateDays} hari.")
            ->line('Denda keterlambatan akan terus bertambah setiap hari sampai buku dikembalikan.')
            ->action('Lihat Detail', route('loans.show', $this->loan));
    }
}
