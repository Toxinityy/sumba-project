<?php

namespace App\Models\Enums;

// Values are the Indonesian school-level abbreviations as they are printed on
// the card badge, so nothing downstream needs a lookup table to display them.
enum SchoolLevel: string
{
    case Tk = 'TK';
    case Smp = 'SMP';
    case Sma = 'SMA';
}
