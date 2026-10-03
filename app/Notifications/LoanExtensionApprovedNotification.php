<?php

namespace App\Notifications;

use App\Models\LoanExtension;
use App\Notifications\Concerns\SendsLibraryNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanExtensionApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable, SendsLibraryNotifications;

    public function __construct(public LoanExtension $extension) {}

    public function via(object $notifiable): array
    {
        return $this->libraryChannels();
    }

    public function toDatabase(object $notifiable): array
    {
        $dueAt = $this->extension->new_due_at?->translatedFormat('d M Y H:i');

        return [
            'title' => 'Perpanjangan disetujui',
            'message' => "Peminjaman {$this->extension->loan->code} diperpanjang {$this->extension->days} hari. Jatuh tempo baru: {$dueAt}.",
            'icon' => 'ni ni-check-bold',
            'url' => route('loans.show', $this->extension->loan),
            'color' => 'from-emerald-500 to-teal-400',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $dueAt = $this->extension->new_due_at?->translatedFormat('d M Y H:i');

        return (new MailMessage)
            ->subject('Perpanjangan Disetujui — '.$this->extension->loan->code)
            ->line("Perpanjangan {$this->extension->days} hari untuk peminjaman {$this->extension->loan->code} telah disetujui.")
            ->line("Jatuh tempo pengembalian sekarang {$dueAt}.")
            ->action('Lihat Detail', route('loans.show', $this->extension->loan));
    }
}
