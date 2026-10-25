<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Exact decimal money arithmetic using bcmath.
 *
 * Money is never held as a PHP float. Values are normalised to a fixed
 * scale-2 decimal string and all arithmetic goes through bcmath.
 */
class Money
{
    public const SCALE = 2;

    public static function normalize(string|int|float $amount): string
    {
        return bcadd(self::toDecimalString($amount), '0', self::SCALE);
    }

    protected static function toDecimalString(string|int|float $amount): string
    {
        if (is_float($amount)) {
            return rtrim(rtrim(sprintf('%.'.(self::SCALE + 4).'F', $amount), '0'), '.');
        }

        $value = trim((string) $amount);

        if ($value === '' || $value === '-') {
            throw new InvalidArgumentException('Invalid money value.');
        }

        return $value;
    }

    public static function add(string|int|float $a, string|int|float $b): string
    {
        return bcadd(self::normalize($a), self::normalize($b), self::SCALE);
    }

    public static function sub(string|int|float $a, string|int|float $b): string
    {
        return bcsub(self::normalize($a), self::normalize($b), self::SCALE);
    }

    public static function compare(string|int|float $a, string|int|float $b): int
    {
        return bccomp(self::normalize($a), self::normalize($b), self::SCALE);
    }

    public static function gt(string|int|float $a, string|int|float $b): bool
    {
        return self::compare($a, $b) > 0;
    }

    public static function gte(string|int|float $a, string|int|float $b): bool
    {
        return self::compare($a, $b) >= 0;
    }

    public static function lt(string|int|float $a, string|int|float $b): bool
    {
        return self::compare($a, $b) < 0;
    }

    public static function lte(string|int|float $a, string|int|float $b): bool
    {
        return self::compare($a, $b) <= 0;
    }

    public static function isZero(string|int|float $a): bool
    {
        return self::compare($a, '0') === 0;
    }

    public static function negate(string|int|float $a): string
    {
        return self::sub('0', $a);
    }

    public static function sum(iterable $items, ?callable $callback = null): string
    {
        $total = '0.00';

        foreach ($items as $item) {
            $value = $callback ? $callback($item) : $item;
            $total = self::add($total, $value);
        }

        return $total;
    }

    /**
     * Format using the Indian numbering system (lakh/crore grouping) with the rupee symbol.
     */
    public static function inr(string|int|float $amount): string
    {
        $value = self::normalize($amount);
        $negative = str_starts_with($value, '-');

        if ($negative) {
            $value = substr($value, 1);
        }

        [$integer, $decimal] = explode('.', $value);
        $lastThree = substr($integer, -3);
        $rest = substr($integer, 0, -3);

        if ($rest !== '') {
            $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            $grouped = $rest.','.$lastThree;
        } else {
            $grouped = $lastThree;
        }

        return ($negative ? '-' : '').'₹'.$grouped.'.'.$decimal;
    }
}
