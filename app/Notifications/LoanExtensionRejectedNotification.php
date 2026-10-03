<?php

namespace App\Notifications;

use App\Models\LoanExtension;
use App\Notifications\Concerns\SendsLibraryNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanExtensionRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable, SendsLibraryNotifications;

    public function __construct(public LoanExtension $extension) {}

    public function via(object $notifiable): array
    {
        return $this->libraryChannels();
    }

    public function toDatabase(object $notifiable): array
    {
        $dueAt = $this->extension->loan->due_at?->translatedFormat('d M Y H:i');

        return [
            'title' => 'Perpanjangan ditolak',
            'message' => "Permintaan perpanjangan untuk peminjaman {$this->extension->loan->code} ditolak: {$this->extension->decision_note} Jatuh tempo tetap {$dueAt}.",
            'icon' => 'ni ni-fat-remove',
            'url' => route('loans.show', $this->extension->loan),
            'color' => 'from-red-600 to-orange-600',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $dueAt = $this->extension->loan->due_at?->translatedFormat('d M Y H:i');

        return (new MailMessage)
            ->subject('Perpanjangan Ditolak — '.$this->extension->loan->code)
            ->line("Permintaan perpanjangan untuk peminjaman {$this->extension->loan->code} tidak dapat disetujui.")
            ->line("Alasan: {$this->extension->decision_note}")
            ->line("Mohon kembalikan buku sebelum {$dueAt}.")
            ->action('Lihat Detail', route('loans.show', $this->extension->loan));
    }
}
