<?php

namespace App\Services\Scraper;

use InvalidArgumentException;

class UrlValidator
{
    /**
     * Validate that a URL is external (not pointing to internal/private IPs).
     * Prevents SSRF attacks.
     */
    public static function validateExternalUrl(string $url): void
    {
        $parsed = parse_url($url);
        $scheme = $parsed['scheme'] ?? '';
        $host = $parsed['host'] ?? '';

        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException('Only HTTP/HTTPS URLs are allowed.');
        }

        if (empty($host)) {
            throw new InvalidArgumentException('URL must contain a valid host.');
        }

        // Resolve hostname to IP and check for internal ranges
        $ip = gethostbyname($host);

        if ($ip === $host) {
            throw new InvalidArgumentException('Could not resolve hostname.');
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            throw new InvalidArgumentException('URL resolves to a private or reserved IP address.');
        }
    }
}
