<?php

namespace App\Notifications;

use App\Models\Loan;
use App\Notifications\Concerns\SendsLibraryNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to staff with loans.approve permission when a member submits a new request. */
class NewLoanPendingNotification extends Notification implements ShouldQueue
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
            'title' => 'Pengajuan peminjaman baru',
            'message' => "{$this->loan->member->name} mengajukan peminjaman {$this->loan->code}.",
            'icon' => 'ni ni-watch-time',
            'url' => route('loans.pending'),
            'color' => 'from-orange-500 to-yellow-500',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pengajuan Peminjaman Baru — '.$this->loan->code)
            ->line("{$this->loan->member->name} mengajukan peminjaman baru ({$this->loan->code}).")
            ->action('Verifikasi Sekarang', route('loans.pending'));
    }
}
