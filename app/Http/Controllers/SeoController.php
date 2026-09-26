<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * robots.txt / sitemap.xml served through the app (not static public files)
 * so the sitemap URL always matches the real APP_URL per environment.
 * Everything behind the 'auth' middleware is disallowed here since crawlers
 * can never authenticate anyway and it keeps "please sign in" pages out of
 * search results.
 */
class SeoController extends Controller
{
    private const DISALLOWED_PREFIXES = [
        '/dashboard', '/profile', '/catalog', '/books', '/book-copies',
        '/categories', '/authors', '/publishers', '/racks', '/members',
        '/loans', '/returns', '/fines', '/reports', '/users', '/audit-logs',
        '/settings', '/security-dashboard', '/notifications',
    ];

    public function robots(): Response
    {
        $lines = ['User-agent: *', 'Allow: /$', 'Allow: /login', 'Allow: /forgot-password'];

        foreach (self::DISALLOWED_PREFIXES as $prefix) {
            $lines[] = "Disallow: {$prefix}";
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.route('sitemap');

        return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain']);
    }

    public function sitemap(): Response
    {
        $urls = [
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('login'), 'priority' => '0.5'],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= '  <url><loc>'.e($url['loc']).'</loc><priority>'.$url['priority'].'</priority></url>'."\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
