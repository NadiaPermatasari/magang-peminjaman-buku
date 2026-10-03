<?php

namespace App\Notifications\Concerns;

use App\Notifications\Channels\FonnteChannel;

/**
 * Shared via()/toFonnte() behaviour for the loan notification classes,
 * all of which follow the same "database always, mail and/or WhatsApp
 * depending on settings" delivery rule (spec §19, §51).
 */
trait SendsLibraryNotifications
{
    /**
     * @return array<int, string>
     */
    protected function libraryChannels(): array
    {
        $channels = setting('email_notification_enabled', true) ? ['database', 'mail'] : ['database'];

        if (setting('whatsapp_notification_enabled', false)) {
            $channels[] = FonnteChannel::class;
        }

        return $channels;
    }

    /**
     * Default WhatsApp text: reuse the same title/message already crafted
     * for the in-app notification (toDatabase), so each notification class
     * doesn't need to author a third, near-identical message body.
     */
    public function toFonnte(object $notifiable): string
    {
        $data = $this->toDatabase($notifiable);

        return trim("*{$data['title']}*\n{$data['message']}");
    }
}
