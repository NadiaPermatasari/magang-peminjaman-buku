<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sends notifications as WhatsApp messages via the Fonnte gateway
 * (https://fonnte.com). A notification opts in by implementing
 * toFonnte($notifiable): string and returning self::class from via().
 *
 * Delivery failures are logged, never thrown: a WhatsApp gateway outage
 * must not affect the database/mail channels of the same notification, or
 * pile up failed-job retries for a non-critical delivery channel.
 */
class FonnteChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toFonnte')) {
            return;
        }

        $token = config('services.fonnte.token');

        if (! $token) {
            return;
        }

        $target = $notifiable->routeNotificationFor('fonnte', $notification);

        if (! $target) {
            return;
        }

        try {
            $response = Http::withHeaders(['Authorization' => $token])
                ->asForm()
                ->post(config('services.fonnte.url'), [
                    'target' => $target,
                    'message' => $notification->toFonnte($notifiable),
                ]);

            if ($response->failed()) {
                Log::warning('Fonnte WhatsApp notification failed', [
                    'target' => $target,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (Throwable $e) {
            Log::warning('Fonnte WhatsApp notification threw an exception', [
                'target' => $target,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
