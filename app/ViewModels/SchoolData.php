<?php

namespace App\ViewModels;

use App\ViewModels\Concerns\ResolvesLocale;

/**
 * FIXTURE — awaiting replacement by App\Models\School (Agent B's lane, see
 * docs/data-contract.md § School). Shape matches that document exactly:
 * ::all() returns the directory shape consumed by <x-cards.school>, ::find()
 * returns the same plus the detail-page fields. At integration, callers
 * swap these static calls for the Eloquent equivalent and no Blade template
 * should need to change.
 *
 * Content is reused from prototype/build.js (the SCHOOLS array and the
 * sekolah-karuni.html page body), which is real reviewed copy for this
 * project, not invented here. Only 'karuni' carries the full detail shape —
 * the prototype itself only built one school detail page, so the other five
 * schools below have directory-only fixtures; visiting their detail route
 * finds nothing and 404s until a real profile exists. That is a known
 * fixture limitation, not a contract violation: the contract does not
 * require every listed school to already have a detail page.
 */
class SchoolData
{
    use ResolvesLocale;

    /** @return array<int, array> Directory shape for every school. */
    public static function all(): array
    {
        return array_map(
            fn (array $school) => self::toDirectoryShape($school),
            array_values(self::schools())
        );
    }

    /** Full shape (directory + detail fields) for one school, or null. */
    public static function find(string $slug): ?array
    {
        return self::schools()[$slug] ?? null;
    }

    private static function toDirectoryShape(array $school): array
    {
        return array_intersect_key($school, array_flip([
            'slug', 'href', 'level', 'name', 'location', 'need', 'status', 'image',
        ]));
    }

    private static function href(string $slug): string
    {
        return route(app()->getLocale().'.schools.show', $slug);
    }

    /** @return array<string, array> Keyed by slug. */
    private static function schools(): array
    {
        return [
            'karuni' => [
                'slug' => 'karuni',
                'href' => self::href('karuni'),
                'level' => 'TK',
                'name' => 'TK Harapan Karuni',
                'location' => 'Karuni, Sumba Barat Daya',
                'need' => self::pick('Ruang baca baru untuk 60 anak.', 'A new reading room for 60 children.'),
                'status' => self::pick('Butuh 4 mitra lagi', 'Needs 4 more partners'),
                'image' => PlaceholderImage::make(800, 1000, self::pick(
                    'Foto: murid-murid TK Harapan Karuni sedang belajar',
                    'Photograph: pupils at TK Harapan Karuni in class'
                )),
                'lede' => self::pick(
                    'Enam puluh anak belajar di dua ruang kelas. Ruang ketiga akan menjadi perpustakaan pertama di desa ini — tempat anak-anak bisa membaca setelah jam sekolah, dan tempat orang tua belajar membaca bersama mereka.',
                    "Sixty children learn in two classrooms. A third room will become the village's first library — somewhere children can read after school, and where parents learn to read alongside them."
                ),
                'people' => [
                    PlaceholderImage::make(800, 1000, self::pick('Potret Ibu Maria Bulu di ruang kelas', 'Portrait of Ibu Maria Bulu in her classroom'))
                        + ['name' => 'Maria Bulu'],
                    PlaceholderImage::make(800, 1000, self::pick('Potret Ibu Yuliana Ndapa di ruang kelas', 'Portrait of Ibu Yuliana Ndapa in her classroom'))
                        + ['name' => 'Yuliana Ndapa'],
                    PlaceholderImage::make(800, 1000, self::pick('Potret Bapak Yosef Praing di halaman sekolah', 'Portrait of Bapak Yosef Praing in the school yard'))
                        + ['name' => 'Yosef Praing'],
                ],
                'context' => [
                    'heading' => self::pick(
                        'Tidak ada tempat membaca setelah pulang sekolah.',
                        'There is nowhere to read after school.'
                    ),
                    'body' => self::pick(
                        'Di Karuni belum ada perpustakaan, dan sebagian besar rumah belum punya aliran listrik untuk membaca setelah gelap. Buku yang ada disimpan di lemari ruang guru dan hanya bisa dipakai saat jam pelajaran.',
                        'Karuni has no library, and most homes have no electricity to read by after dark. The books the school owns are kept in a cupboard in the staff room and can only be used during lessons.'
                    ),
                    'image' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: lemari buku di ruang guru, sore hari',
                        'Photograph: the book cupboard in the staff room, late afternoon'
                    )),
                ],
                'work' => [
                    'heading' => self::pick(
                        'Ruang ketiga menjadi perpustakaan.',
                        'The third room becomes a library.'
                    ),
                    'body' => self::pick(
                        'Pekerjaan bangunan hampir selesai. Yang belum tersedia adalah rak untuk sisi kedua ruangan, koleksi buku berbahasa Indonesia untuk usia dini, dan satu panel surya kecil agar ruangan bisa dipakai sampai malam.',
                        'The building work is nearly done. What is still missing is shelving for the second side of the room, a collection of early-years books in Indonesian, and one small solar panel so the room can be used into the evening.'
                    ),
                    'image' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: rangka atap dan rak pertama terpasang',
                        'Photograph: roof frame and the first shelving installed'
                    )),
                ],
                'evidence' => [
                    'before' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: ruang ketiga sebelum dikerjakan',
                        'Photograph: the third room before work began'
                    )) + ['caption' => self::pick('Maret 2026 — ruang ketiga, belum terpakai', 'March 2026 — the third room, unused')],
                    'after' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: rangka atap dan rak pertama terpasang',
                        'Photograph: roof frame and the first shelving installed'
                    )) + ['caption' => self::pick('Agustus 2026 — atap dan rak pertama terpasang', 'August 2026 — roof and first shelving in place')],
                ],
                'facts' => [
                    ['key' => self::pick('Dibuka', 'Opened'), 'value' => '2009'],
                    ['key' => self::pick('Murid', 'Pupils'), 'value' => '60'],
                    ['key' => self::pick('Guru', 'Teachers'), 'value' => '3'],
                    ['key' => self::pick('Biaya bagi keluarga', 'Cost to families'), 'value' => self::pick('Gratis', 'Free')],
                ],
            ],

            'anakalang' => [
                'slug' => 'anakalang',
                'href' => self::href('anakalang'),
                'level' => 'SMP',
                'name' => 'SMP Harapan Anakalang',
                'location' => 'Anakalang, Sumba Tengah',
                'need' => self::pick('Guru IPA untuk tahun ajaran baru.', 'A science teacher for the new school year.'),
                'status' => self::pick('Sedang mencari mitra pendidik', 'Seeking a teaching partner'),
                'image' => PlaceholderImage::make(800, 1000, self::pick(
                    'Foto: murid-murid SMP Harapan Anakalang sedang belajar',
                    'Photograph: pupils at SMP Harapan Anakalang in class'
                )),
            ],

            'kambera' => [
                'slug' => 'kambera',
                'href' => self::href('kambera'),
                'level' => 'SMA',
                'name' => 'SMA Harapan Kambera',
                'location' => 'Kambera, Sumba Timur',
                'need' => self::pick('Pembaruan laboratorium komputer.', 'Computer laboratory refurbishment.'),
                'status' => self::pick('Perlu 2 mitra korporasi', 'Needs 2 corporate partners'),
                'image' => PlaceholderImage::make(800, 1000, self::pick(
                    'Foto: murid-murid SMA Harapan Kambera sedang belajar',
                    'Photograph: pupils at SMA Harapan Kambera in class'
                )),
            ],

            'melolo' => [
                'slug' => 'melolo',
                'href' => self::href('melolo'),
                'level' => 'TK',
                'name' => 'TK Harapan Melolo',
                'location' => 'Melolo, Sumba Timur',
                'need' => self::pick('Perlengkapan bermain dan belajar.', 'Play and learning equipment.'),
                'status' => self::pick('Didanai penuh tahun ini', 'Fully funded this year'),
                'image' => PlaceholderImage::make(800, 1000, self::pick(
                    'Foto: murid-murid TK Harapan Melolo sedang bermain',
                    'Photograph: pupils at TK Harapan Melolo at play'
                )),
            ],

            'lewa' => [
                'slug' => 'lewa',
                'href' => self::href('lewa'),
                'level' => 'SMP',
                'name' => 'SMP Harapan Lewa',
                'location' => 'Lewa, Sumba Timur',
                'need' => self::pick('Asrama putri untuk murid dari desa jauh.', "A girls' dormitory for pupils from distant villages."),
                'status' => self::pick('Pembangunan sedang berjalan', 'Construction underway'),
                'image' => PlaceholderImage::make(800, 1000, self::pick(
                    'Foto: murid-murid SMP Harapan Lewa sedang belajar',
                    'Photograph: pupils at SMP Harapan Lewa in class'
                )),
            ],

            'waikabubak' => [
                'slug' => 'waikabubak',
                'href' => self::href('waikabubak'),
                'level' => 'SMA',
                'name' => 'SMA Harapan Waikabubak',
                'location' => 'Waikabubak, Sumba Barat',
                'need' => self::pick('Beasiswa kelas akhir untuk 12 murid.', 'Final-year scholarships for 12 pupils.'),
                'status' => self::pick('Butuh 5 mitra lagi', 'Needs 5 more partners'),
                'image' => PlaceholderImage::make(800, 1000, self::pick(
                    'Foto: murid-murid SMA Harapan Waikabubak sedang belajar',
                    'Photograph: pupils at SMA Harapan Waikabubak in class'
                )),
            ],
        ];
    }
}
