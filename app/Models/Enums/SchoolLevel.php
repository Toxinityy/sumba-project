<?php

namespace App\Models\Enums;

// Values are the Indonesian school-level abbreviations as they are printed on
// the card badge, so nothing downstream needs a lookup table to display them.
enum SchoolLevel: string
{
    case Tk = 'TK';
    case Smp = 'SMP';
    case Sma = 'SMA';

    /**
     * Derived, never stored, so a school's level and its age range cannot
     * disagree (docs/data-contract.md § School).
     */
    public function ageRange(string $locale): string
    {
        [$from, $to] = match ($this) {
            self::Tk => [4, 6],
            self::Smp => [12, 15],
            self::Sma => [15, 18],
        };

        return $locale === 'en' ? "ages {$from}-{$to}" : "{$from}-{$to} tahun";
    }
}
