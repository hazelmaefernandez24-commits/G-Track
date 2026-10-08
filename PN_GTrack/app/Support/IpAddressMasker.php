<?php

namespace App\Support;

class IpAddressMasker
{
    public static function maskInText(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        return preg_replace_callback(
            '/(?<![\d.])(?:\d{1,3}\.){3}\d{1,3}(?![\d.])/',
            static function (array $matches): string {
                $ip = $matches[0];

                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
                    return $ip;
                }

                $octets = explode('.', $ip);

                return $octets[0] . '.***.*.*' . substr($octets[3], -2);
            },
            $text
        );
    }
}
