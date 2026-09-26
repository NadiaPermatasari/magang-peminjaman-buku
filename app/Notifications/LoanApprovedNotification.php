<?php

namespace App\Notifications;

use App\Models\Loan;
use App\Notifications\Concerns\SendsLibraryNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable, SendsLibraryNotifications;

    public function __construct(public Loan $loan) {}

    public function via(object $notifiable): array
    {
        return $this->libraryChannels();
    }

    public function toDatabase(object $notifiable): array
    {
        $deadline = $this->loan->pickup_deadline?->translatedFormat('d M Y H:i');

        return [
            'title' => 'Peminjaman disetujui',
            'message' => "Pengajuan {$this->loan->code} disetujui. Ambil buku sebelum {$deadline}.",
            'icon' => 'ni ni-check-bold',
            'url' => route('loans.show', $this->loan),
            'color' => 'from-emerald-500 to-teal-400',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $deadline = $this->loan->pickup_deadline?->translatedFormat('d M Y H:i');

        return (new MailMessage)
            ->subject('Peminjaman Disetujui — '.$this->loan->code)
            ->line("Pengajuan peminjaman {$this->loan->code} telah disetujui.")
            ->line("Mohon ambil buku Anda sebelum {$deadline}, jika tidak pengajuan akan kedaluwarsa.")
            ->action('Lihat Detail', route('loans.show', $this->loan));
    }
}
