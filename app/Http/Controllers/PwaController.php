<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PwaController extends Controller
{
    public function manifest(): JsonResponse
    {
        return response()->json([
            'id' => route('home', [], false),
            'name' => app_name(),
            'short_name' => setting('app_short_name') ?: app_name(),
            'description' => setting('description') ?: 'Layanan peminjaman arsip berkas pertanahan.',
            'lang' => 'id',
            'start_url' => route('dashboard', [], false),
            'scope' => route('home', [], false),
            'display' => 'standalone',
            'background_color' => '#f8fafc',
            'theme_color' => setting('primary_color'),
            'prefer_related_applications' => false,
            'icons' => [
                [
                    'src' => url('pwa/icons/icon-192.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => url('pwa/icons/icon-512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                ],
            ],
        ], 200, [
            'Content-Type' => 'application/manifest+json',
            'Cache-Control' => 'no-cache',
        ]);
    }

    public function offline(): Response
    {
        // This public page is the only HTML stored by the service worker.
        // Keep it independent of the session and of the shared app layout.
        return response()->view('pwa.offline', [], 200, [
            'Cache-Control' => 'no-cache',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }
}
