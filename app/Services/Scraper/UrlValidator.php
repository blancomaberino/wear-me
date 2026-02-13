<?php

namespace App\Services\Scraper;

use InvalidArgumentException;

class UrlValidator
{
    /**
     * Validate that a URL is external (not pointing to internal/private IPs).
     * Prevents SSRF attacks including DNS rebinding.
     *
     * Returns the resolved IP so callers can pin it for the HTTP request.
     */
    public static function validateExternalUrl(string $url): string
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

        // Check IPv4 (A records)
        $ip = gethostbyname($host);

        if ($ip === $host) {
            throw new InvalidArgumentException('Could not resolve hostname.');
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            throw new InvalidArgumentException('URL resolves to a private or reserved IP address.');
        }

        // Check IPv6 (AAAA records) if available
        $records = @dns_get_record($host, DNS_AAAA);
        if ($records) {
            foreach ($records as $record) {
                $ipv6 = $record['ipv6'] ?? null;
                if ($ipv6 && filter_var($ipv6, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                    throw new InvalidArgumentException('URL resolves to a private or reserved IPv6 address.');
                }
            }
        }

        return $ip;
    }

    /**
     * Get Guzzle options that pin DNS to the validated IP and disable redirects.
     */
    public static function getSecureRequestOptions(string $url, string $resolvedIp): array
    {
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '';
        $port = $parsed['port'] ?? ($parsed['scheme'] === 'https' ? 443 : 80);

        return [
            'curl' => [
                CURLOPT_RESOLVE => ["{$host}:{$port}:{$resolvedIp}"],
            ],
            'allow_redirects' => false,
        ];
    }
}
