<?php

namespace App\ViewModels;

use App\ViewModels\Concerns\ResolvesLocale;

/**
 * FIXTURE — awaiting replacement by App\Models\Stat (Agent B's lane, see
 * docs/data-contract.md § Stat). Content reused from prototype/build.js's
 * home page stat band.
 */
class StatData
{
    use ResolvesLocale;

    /** @return array<int, array{value: string, label: string, asOf: string}> */
    public static function all(): array
    {
        return [
            [
                'value' => '14',
                'label' => self::pick('Sekolah dan rumah anak yang aktif', 'Schools and children\'s homes running'),
                'asOf' => self::pick('Per Agustus 2026', 'As of August 2026'),
            ],
            [
                'value' => '612',
                'label' => self::pick('Anak bersekolah tahun ini', 'Children in school this year'),
                'asOf' => self::pick('Per Agustus 2026', 'As of August 2026'),
            ],
            [
                'value' => '19',
                'label' => self::pick('Tahun bekerja di Sumba', 'Years working in Sumba'),
                'asOf' => self::pick('Sejak 2007', 'Since 2007'),
            ],
        ];
    }
}
