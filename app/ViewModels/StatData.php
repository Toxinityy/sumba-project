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

    /**
     * The landing page's scale section, in narrative order: the number the page
     * is about first, at display scale, then the two that qualify it.
     *
     * A separate method rather than a reordering of ::all(), because ::all()'s
     * order is what the Impact page and the deep-page stat band already read.
     *
     * @return array<int, array{value: string, label: string, asOf: string}>
     */
    public static function scale(): array
    {
        $byValue = collect(self::all())->keyBy('value');

        return array_values(array_filter([
            $byValue->get('612'),
            $byValue->get('14'),
            $byValue->get('19'),
        ]));
    }

    /**
     * The landing page's challenge ledger — the same Stat shape read as a
     * figure ledger rather than a band.
     *
     * THE DIGNITY RULE. Every measure here is a circumstance or a gap in the
     * system: how far the walk was, a post nobody has filled, a village with
     * no grid connection. None of them is an attribute of a child or a family.
     * "0 permanent science teachers" is in scope; "these children are behind"
     * is not — that sentence turns a person into a symptom, and it reads as
     * amateur to the institutional donors this site is written for as much as
     * it fails the people in the photograph.
     *
     * `value` is deliberately not always a number: "Setelah gelap" / "After
     * dark" is a measure too. `body` is an optional Stat key used only by this
     * variant — the ledger sets `label` as the bolded opening clause and
     * `body` as the sentence that finishes it. See
     * docs/landing-redesign-report.md.
     *
     * @return array<int, array{value: string, label: string, body: string}>
     */
    public static function challenge(): array
    {
        return [
            [
                'value' => self::pick('3 jam', '3 hours'),
                'label' => self::pick('Berjalan kaki ke sekolah terdekat', 'On foot to the nearest school'),
                'body' => self::pick(
                    'sebelum ruang kelas pertama dibuka di Karuni pada 2007. Tujuh anak berkumpul di bawah atap seng karena tidak ada pilihan lain yang lebih dekat.',
                    'before the first classroom opened in Karuni in 2007. Seven children gathered under a tin roof because nothing closer existed.'
                ),
            ],
            [
                'value' => '0',
                'label' => self::pick('Guru IPA tetap di SMP Harapan Anakalang', 'Permanent science teachers at SMP Harapan Anakalang'),
                'body' => self::pick(
                    'sejak awal tahun ini. Seratus sepuluh murid belajar sains dari buku teks saja, dan kepala sekolah menutup kelasnya di antara jam pelajarannya sendiri.',
                    'since the start of this year. A hundred and ten pupils learn science from a textbook alone, and the head teacher covers the lessons between her own.'
                ),
            ],
            [
                'value' => self::pick('Setelah gelap', 'After dark'),
                'label' => self::pick('Belum ada aliran listrik', 'Still no grid electricity'),
                'body' => self::pick(
                    'di sebagian besar rumah di Karuni, dan desa ini belum punya perpustakaan. Buku sekolah disimpan di lemari ruang guru dan hanya bisa dipakai saat jam pelajaran.',
                    'in most houses in Karuni, and the village has no library. School books live in a cupboard in the staff room and can only be read in lesson time.'
                ),
            ],
        ];
    }
}
