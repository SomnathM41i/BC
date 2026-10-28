<?php

namespace App\Support;

/**
 * Normalizes Indian mobile numbers to a bare 10-digit form.
 *
 * The same helper is used when saving a client number on a project and when a
 * visitor looks their records up, so both sides compare the same value.
 */
final class PhoneNumber
{
    public static function normalize(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        if ($digits === null || $digits === '') {
            return null;
        }

        // Accept +91 / 0091 / 0 prefixes, but nothing longer than an Indian
        // mobile number once those prefixes are removed.
        if (str_starts_with($digits, '0091') && strlen($digits) === 14) {
            $digits = substr($digits, 4);
        } elseif (str_starts_with($digits, '91') && strlen($digits) === 12) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0') && strlen($digits) === 11) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) !== 10 || preg_match('/^[6-9]\d{9}$/', $digits) !== 1) {
            return null;
        }

        return $digits;
    }

    /**
     * A readable form of a verified Indian mobile: +91 90211 13887.
     */
    public static function display(?string $value): ?string
    {
        $normalized = self::normalize($value);

        if ($normalized === null) {
            return null;
        }

        return '+91 '.substr($normalized, 0, 5).' '.substr($normalized, 5);
    }

    /**
     * A stable, non-reversible key for logging/rate limiting without storing
     * the visitor's number.
     */
    public static function hash(?string $value): ?string
    {
        $normalized = self::normalize($value);

        return $normalized === null ? null : hash('sha256', $normalized);
    }
}
