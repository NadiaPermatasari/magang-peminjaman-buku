<?php

namespace App\Support;

/**
 * Minimal user-agent parser for the "Browser sessions" list on the profile page.
 */
class UserAgent
{
    public function __construct(protected string $userAgent) {}

    public static function parse(?string $userAgent): self
    {
        return new self((string) $userAgent);
    }

    public function platform(): string
    {
        return match (true) {
            (bool) preg_match('/windows/i', $this->userAgent) => 'Windows',
            (bool) preg_match('/iphone|ipad|ipod/i', $this->userAgent) => 'iOS',
            (bool) preg_match('/mac os x|macintosh/i', $this->userAgent) => 'macOS',
            (bool) preg_match('/android/i', $this->userAgent) => 'Android',
            (bool) preg_match('/cros/i', $this->userAgent) => 'Chrome OS',
            (bool) preg_match('/linux/i', $this->userAgent) => 'Linux',
            default => 'Unknown',
        };
    }

    public function browser(): string
    {
        return match (true) {
            (bool) preg_match('/edg\//i', $this->userAgent) => 'Edge',
            (bool) preg_match('/opr\/|opera/i', $this->userAgent) => 'Opera',
            (bool) preg_match('/samsungbrowser/i', $this->userAgent) => 'Samsung Internet',
            (bool) preg_match('/chrome|crios/i', $this->userAgent) => 'Chrome',
            (bool) preg_match('/firefox|fxios/i', $this->userAgent) => 'Firefox',
            (bool) preg_match('/safari/i', $this->userAgent) => 'Safari',
            (bool) preg_match('/curl|postman|insomnia/i', $this->userAgent) => 'API client',
            default => 'Unknown',
        };
    }

    /**
     * "desktop", "tablet" or "mobile".
     */
    public function device(): string
    {
        return match (true) {
            (bool) preg_match('/ipad|tablet|(android(?!.*mobile))/i', $this->userAgent) => 'tablet',
            (bool) preg_match('/mobile|iphone|ipod|android|blackberry|opera mini|iemobile/i', $this->userAgent) => 'mobile',
            default => 'desktop',
        };
    }
}
