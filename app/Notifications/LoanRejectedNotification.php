<?php

namespace App\Notifications;

use App\Models\Loan;
use App\Notifications\Concerns\SendsLibraryNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanRejectedNotification extends Notification implements ShouldQueue
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
            'title' => 'Peminjaman ditolak',
            'message' => "Pengajuan {$this->loan->code} ditolak: {$this->loan->rejection_reason}",
            'icon' => 'ni ni-fat-remove',
            'url' => route('loans.show', $this->loan),
            'color' => 'from-red-600 to-orange-600',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Peminjaman Ditolak — '.$this->loan->code)
            ->line("Mohon maaf, pengajuan peminjaman {$this->loan->code} ditolak.")
            ->line("Alasan: {$this->loan->rejection_reason}")
            ->action('Lihat Detail', route('loans.show', $this->loan));
    }
}
