<?php

namespace App\Notifications;

use App\Models\Loan;
use App\Notifications\Concerns\SendsLibraryNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanPickupReminderNotification extends Notification implements ShouldQueue
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
            'title' => 'Batas pengambilan hampir habis',
            'message' => "Segera ambil buku untuk pengajuan {$this->loan->code} sebelum {$deadline}.",
            'icon' => 'ni ni-time-alarm',
            'url' => route('loans.show', $this->loan),
            'color' => 'from-orange-500 to-yellow-500',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $deadline = $this->loan->pickup_deadline?->translatedFormat('d M Y H:i');

        return (new MailMessage)
            ->subject('Pengingat Pengambilan Buku — '.$this->loan->code)
            ->line("Batas waktu pengambilan buku untuk pengajuan {$this->loan->code} adalah {$deadline}.")
            ->line('Jika tidak diambil sebelum batas waktu, pengajuan akan kedaluwarsa secara otomatis.')
            ->action('Lihat Detail', route('loans.show', $this->loan));
    }
}
