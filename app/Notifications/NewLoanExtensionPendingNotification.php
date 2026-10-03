<?php

namespace App\Notifications;

use App\Models\LoanExtension;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi in-app untuk petugas/admin: ada pengajuan perpanjangan baru
 * yang menunggu keputusan. Hanya kanal database — antrean kerja petugas
 * tidak perlu mengirim email/WA ke seluruh staf.
 */
class NewLoanExtensionPendingNotification extends Notification
{
    use Queueable;

    public function __construct(public LoanExtension $extension) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $member = $this->extension->loan->member;

        return [
            'title' => 'Pengajuan perpanjangan baru',
            'message' => "{$member->name} meminta perpanjangan {$this->extension->days} hari untuk peminjaman {$this->extension->loan->code}.",
            'icon' => 'ni ni-calendar-grid-58',
            'url' => route('loan-extensions.index'),
            'color' => 'from-orange-500 to-yellow-500',
        ];
    }
}
