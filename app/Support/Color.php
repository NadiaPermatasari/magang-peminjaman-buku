<?php

namespace App\Support;

class Color
{
    /**
     * "#5e72e4" -> "94 114 228" (space-separated decimal RGB), the format
     * Tailwind's rgb(var(--x) / <alpha-value>) color pattern expects.
     * Falls back to Argon's original primary color on any invalid input —
     * this feeds a <style> block, never trust it blindly.
     */
    public static function hexToRgbTriplet(?string $hex, string $default = '94 114 228'): string
    {
        if (! $hex || ! preg_match('/^#?([0-9a-fA-F]{6})$/', $hex, $matches)) {
            return $default;
        }

        [$r, $g, $b] = sscanf($matches[1], '%02x%02x%02x');

        return "{$r} {$g} {$b}";
    }
}
