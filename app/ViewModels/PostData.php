<?php

namespace App\ViewModels;

use App\ViewModels\Concerns\ResolvesLocale;

/**
 * FIXTURE — awaiting replacement by App\Models\Post (Agent B's lane, see
 * docs/data-contract.md § Post). Content reused from prototype/build.js's
 * STORIES array and cerita-detail.html (the Rambu story) where it exists.
 *
 * Safeguarding: 'name' for a minor subject is a first name only, by
 * construction — see the contract's note that minors get no surname field
 * at all, and cards/story.blade.php's comment against concatenating one on.
 * A named child is never tied to a specific location plus a routine in the
 * same sentence — see each profile's `body` below.
 *
 * Pass 4 (2026-09-18): story detail pages now exist (`stories.show`), so
 * `href` points at the real per-post URL instead of the stories index.
 * Each profile gained `title` and `body` (contract fields) plus a `quote`
 * key — an addition beyond the contract's base Post shape, the same way
 * SchoolData adds detail-only fields beyond the base School shape. `body`
 * for Ibu Maria Bulu and Umbu is placeholder narrative invented for this
 * pass (not prototype-sourced, which only wrote Rambu's story in full);
 * Rambu's body/quote are the prototype's own reviewed copy.
 *
 * Two `kind: 'photo-essay'` entries back the Gallery page (spec §6: "a
 * gallery needs no model of its own... photo essays are posts whose body
 * is mostly image blocks"). Their detail pages are out of scope for this
 * pass — deferred alongside Gallery itself (spec §10) — so their `href`
 * points at the gallery index rather than a `stories.show` route that
 * would render a Lede/Quote spine with no matching content.
 */
class PostData
{
    use ResolvesLocale;

    /** @return array<int, array> */
    public static function recent(int $n): array
    {
        return array_slice(self::profiles(), 0, $n);
    }

    /** One post (profile or photo essay) by slug, or null. */
    public static function find(string $slug): ?array
    {
        return collect(self::profiles())
            ->merge(self::photoEssays())
            ->firstWhere('slug', $slug);
    }

    /** @return array<int, array> Photo-essay posts, for the Gallery page. */
    public static function photoEssays(): array
    {
        return self::photoEssaysData();
    }

    private static function profiles(): array
    {
        return [
            [
                'slug' => 'rambu-sembilan-kilometer',
                'href' => self::href('rambu-sembilan-kilometer'),
                'kind' => 'profile',
                'name' => 'Rambu',
                'title' => self::pick('Sembilan kilometer, setiap pagi.', 'Nine kilometres, every morning.'),
                'hook' => self::pick(
                    'Berjalan sembilan kilometer setiap pagi — sekarang ia mengajar adik kelasnya membaca.',
                    'She walked nine kilometres each morning — now she teaches the younger pupils to read.'
                ),
                'body' => self::pick(
                    '<p>Rambu berangkat pukul lima pagi. Jalan dari kampungnya menurun melewati padang, menyeberangi satu sungai kecil yang meluap di musim hujan, lalu naik lagi ke jalan beraspal tempat sekolahnya berada. Sembilan kilometer. Ia melakukannya selama tiga tahun.</p><p>Tahun ini ia mengajar membaca untuk murid kelas satu dua kali seminggu, sebelum jam pelajarannya sendiri dimulai. Gurunya bilang ia menjelaskan lebih sabar daripada sebagian orang dewasa.</p>',
                    '<p>Rambu leaves at five in the morning. The path from her hamlet drops down across the grassland, crosses a small river that floods in the wet season, then climbs back up to the paved road where her school stands. Nine kilometres. She has done it for three years.</p><p>This year she teaches reading to the first-year pupils twice a week, before her own lessons begin. Her teacher says she explains things more patiently than some adults do.</p>'
                ),
                'quote' => [
                    'text' => self::pick('“Saya ingin jadi guru. Bukan di kota — di sini.”', '“I want to be a teacher. Not in the city — here.”'),
                    'attribution' => 'Rambu',
                    'role' => self::pick('Kelas akhir, SMA Harapan Kambera', 'Final year, SMA Harapan Kambera'),
                ],
                'image' => PlaceholderImage::make(800, 1000, self::pick(
                    'Foto: potret lingkungan Rambu',
                    'Photograph: environmental portrait of Rambu'
                )),
                'published_at' => '2026-08-14',
            ],
            [
                'slug' => 'ibu-maria-bulu',
                'href' => self::href('ibu-maria-bulu'),
                'kind' => 'profile',
                'name' => 'Ibu Maria Bulu',
                'title' => self::pick('Sebelas tahun merantau, lalu pulang untuk mengajar.', 'Eleven years away, then home to teach.'),
                'hook' => self::pick(
                    'Kepala sekolah yang kembali ke desanya setelah sebelas tahun merantau.',
                    'A head teacher who came back to her village after eleven years away.'
                ),
                'body' => self::pick(
                    '<p>Ibu Maria mengajar di Kupang selama sebelas tahun sebelum sebuah surat dari desanya sendiri mengubah rencananya: TK satu-satunya di Karuni kehilangan kepala sekolahnya, dan tidak ada pengganti.</p><p>Ia pulang tahun 2019. Sejak itu jumlah murid TK Harapan Karuni naik dari dua puluh menjadi enam puluh, dan ruang ketiga yang sedang dibangun adalah idenya sendiri.</p>',
                    "<p>Ibu Maria taught in Kupang for eleven years before a letter from her own village changed her plans: the only kindergarten in Karuni had lost its head teacher, with no replacement in sight.</p><p>She came home in 2019. Since then, enrolment at TK Harapan Karuni has grown from twenty pupils to sixty, and the third room now under construction was her own idea.</p>"
                ),
                'quote' => [
                    'text' => self::pick('“Anak-anak di sini sama pintarnya. Mereka hanya butuh ruang.”', '“The children here are just as capable. They only need the room.”'),
                    'attribution' => 'Ibu Maria Bulu',
                    'role' => self::pick('Kepala sekolah, TK Harapan Karuni', 'Head teacher, TK Harapan Karuni'),
                ],
                'image' => PlaceholderImage::make(800, 1000, self::pick(
                    'Foto: potret lingkungan Ibu Maria Bulu',
                    'Photograph: environmental portrait of Ibu Maria Bulu'
                )),
                'published_at' => '2026-07-02',
            ],
            [
                'slug' => 'umbu-elektronika',
                'href' => self::href('umbu-elektronika'),
                'kind' => 'profile',
                'name' => 'Umbu',
                'title' => self::pick('Ia membongkar radio rusak untuk belajar sendiri.', 'He took broken radios apart to teach himself.'),
                'hook' => self::pick(
                    'Ia membongkar radio rusak untuk belajar elektronika. Kini ia di kelas akhir SMA.',
                    'He took apart broken radios to teach himself electronics. He is now in his final year.'
                ),
                'body' => self::pick(
                    '<p>Umbu mulai membongkar radio rusak milik tetangganya sejak kelas lima, hanya untuk melihat bagaimana bagian dalamnya bekerja. Tidak ada kelas elektronika di sekolahnya waktu itu — ia belajar dari mencoba dan gagal berulang kali.</p><p>Sekarang, di kelas akhir, ia salah satu dari dua belas murid yang memenuhi syarat masuk universitas tahun ini. Ia berharap bisa belajar teknik.</p>',
                    "<p>Umbu started taking apart his neighbours' broken radios in fifth grade, just to see how the parts fit together. There was no electronics class at his school back then — he learned by trying and failing, repeatedly.</p><p>Now, in his final year, he is one of twelve pupils who qualify for university entry this year. He hopes to study engineering.</p>"
                ),
                'quote' => [
                    'text' => self::pick('“Kalau bisa dibongkar, bisa dipahami.”', '“If it can be taken apart, it can be understood.”'),
                    'attribution' => 'Umbu',
                    'role' => self::pick('Kelas akhir, SMA Harapan Waikabubak', 'Final year, SMA Harapan Waikabubak'),
                ],
                'image' => PlaceholderImage::make(800, 1000, self::pick(
                    'Foto: potret lingkungan Umbu',
                    'Photograph: environmental portrait of Umbu'
                )),
                'published_at' => '2026-05-20',
            ],
        ];
    }

    private static function photoEssaysData(): array
    {
        $galleryIndex = route(app()->getLocale().'.gallery.index');

        return [
            [
                'slug' => 'panen-raya-karuni',
                'href' => $galleryIndex,
                'kind' => 'photo-essay',
                'name' => self::pick('Panen bersama di Karuni', 'A shared harvest in Karuni'),
                'hook' => self::pick(
                    'Orang tua dan guru bekerja sama menyiapkan tanah untuk kebun sekolah.',
                    'Parents and teachers working the ground together for the school garden.'
                ),
                'image' => PlaceholderImage::make(1200, 800, self::pick(
                    'Foto: esai foto — warga desa menyiapkan lahan kebun sekolah',
                    'Photograph: photo essay — villagers preparing the school garden plot'
                )),
                'published_at' => '2026-06-10',
            ],
            [
                'slug' => 'hari-pertama-sekolah',
                'href' => $galleryIndex,
                'kind' => 'photo-essay',
                'name' => self::pick('Hari pertama tahun ajaran baru', 'The first day of the new school year'),
                'hook' => self::pick(
                    'Seragam baru, buku baru, dan halaman sekolah yang ramai lagi.',
                    'New uniforms, new books, and a school yard full again.'
                ),
                'image' => PlaceholderImage::make(1200, 800, self::pick(
                    'Foto: esai foto — murid berkumpul di halaman pada hari pertama sekolah',
                    'Photograph: photo essay — pupils gathering in the yard on the first day of school'
                )),
                'published_at' => '2026-07-15',
            ],
        ];
    }

    private static function href(string $slug): string
    {
        return route(app()->getLocale().'.stories.show', $slug);
    }
}
