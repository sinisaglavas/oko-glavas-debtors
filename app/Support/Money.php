<?php

namespace App\Support;

use InvalidArgumentException;

final class Money
{
    public static function toMinor(string $value): int
    {
        $normalized = str_replace(',', '.', trim($value));

        if (! preg_match('/^\d+(?:\.\d{1,2})?$/', $normalized)) {
            throw new InvalidArgumentException('Iznos mora biti pozitivan broj sa najviše dve decimale.');
        }

        [$whole, $fraction] = array_pad(explode('.', $normalized, 2), 2, '');
        $minor = ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');

        if ($minor < 1) {
            throw new InvalidArgumentException('Iznos mora biti veći od nule.');
        }

        return $minor;
    }
}
