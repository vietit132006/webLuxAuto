<?php

namespace App\Support;

class YouTubeVideo
{
    public static function extractId(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (self::isValidId($value)) {
            return $value;
        }

        $patterns = [
            '~youtube\.com/watch\?(?:.*&)?v=([A-Za-z0-9_-]{11})~i',
            '~youtu\.be/([A-Za-z0-9_-]{11})~i',
            '~youtube\.com/embed/([A-Za-z0-9_-]{11})~i',
            '~studio\.youtube\.com/video/([A-Za-z0-9_-]{11})~i',
            '~youtube\.com/shorts/([A-Za-z0-9_-]{11})~i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value, $matches)) {
                return $matches[1];
            }
        }

        $parts = parse_url($value);

        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
            $candidate = $query['v'] ?? null;

            if (self::isValidId($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    public static function isValidId(?string $value): bool
    {
        return is_string($value) && preg_match('/^[A-Za-z0-9_-]{11}$/', $value) === 1;
    }
}
