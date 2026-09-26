<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Generic in-app (database) notification shown in the navbar bell and on the
 * Notifications page.
 *
 *   $user->notify(new AppNotification('Welcome!', 'Thanks for joining.', 'ni ni-like-2', route('dashboard')));
 */
class AppNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message = '',
        public string $icon = 'ni ni-bell-55',
        public ?string $url = null,
        public string $color = 'from-blue-500 to-violet-500',
    ) {}

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
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'icon' => $this->icon,
            'url' => $this->url,
            'color' => $this->color,
        ];
    }
}
