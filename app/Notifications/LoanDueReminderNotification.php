<?php

namespace App\Notifications;

use App\Models\Loan;
use App\Notifications\Concerns\SendsLibraryNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanDueReminderNotification extends Notification implements ShouldQueue
{
    use Queueable, SendsLibraryNotifications;

    public function __construct(public Loan $loan, public int $daysRemaining) {}

    public function via(object $notifiable): array
    {
        return $this->libraryChannels();
    }

    private function message(): string
    {
        return match (true) {
            $this->daysRemaining <= 0 => "Buku pada peminjaman {$this->loan->code} jatuh tempo hari ini.",
            default => "Buku pada peminjaman {$this->loan->code} jatuh tempo dalam {$this->daysRemaining} hari.",
        };
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Pengingat jatuh tempo',
            'message' => $this->message(),
            'icon' => 'ni ni-time-alarm',
            'url' => route('loans.show', $this->loan),
            'color' => 'from-orange-500 to-yellow-500',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pengingat Jatuh Tempo — '.$this->loan->code)
            ->line($this->message())
            ->action('Lihat Detail', route('loans.show', $this->loan));
    }
}
