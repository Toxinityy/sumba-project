<?php

namespace App\ViewModels;

use App\ViewModels\Concerns\ResolvesLocale;

/**
 * FIXTURE — awaiting replacement by App\Models\Post (Agent B's lane, see
 * docs/data-contract.md § Post). Content reused from prototype/build.js's
 * STORIES array.
 *
 * Safeguarding: 'name' for these three is a first name only, by construction
 * — see the contract's note that minors get no surname field at all, and
 * cards/story.blade.php's comment against concatenating one on. Post detail
 * pages are out of scope for this build (deferred per spec §10), so `href`
 * points at the stories index rather than a per-post route that doesn't
 * exist yet — no dead link, no invented route.
 */
class PostData
{
    use ResolvesLocale;

    /** @return array<int, array> */
    public static function recent(int $n): array
    {
        return array_slice(self::posts(), 0, $n);
    }

    private static function posts(): array
    {
        $storiesIndex = route(app()->getLocale().'.stories.index');

        return [
            [
                'slug' => 'rambu-sembilan-kilometer',
                'href' => $storiesIndex,
                'kind' => 'profile',
                'name' => 'Rambu',
                'hook' => self::pick(
                    'Berjalan sembilan kilometer setiap pagi — sekarang ia mengajar adik kelasnya membaca.',
                    'She walked nine kilometres each morning — now she teaches the younger pupils to read.'
                ),
                'image' => PlaceholderImage::make(800, 1000, self::pick(
                    'Foto: potret lingkungan Rambu',
                    'Photograph: environmental portrait of Rambu'
                )),
                'published_at' => '2026-08-14',
            ],
            [
                'slug' => 'ibu-maria-bulu',
                'href' => $storiesIndex,
                'kind' => 'profile',
                'name' => 'Ibu Maria Bulu',
                'hook' => self::pick(
                    'Kepala sekolah yang kembali ke desanya setelah sebelas tahun merantau.',
                    'A head teacher who came back to her village after eleven years away.'
                ),
                'image' => PlaceholderImage::make(800, 1000, self::pick(
                    'Foto: potret lingkungan Ibu Maria Bulu',
                    'Photograph: environmental portrait of Ibu Maria Bulu'
                )),
                'published_at' => '2026-07-02',
            ],
            [
                'slug' => 'umbu-elektronika',
                'href' => $storiesIndex,
                'kind' => 'profile',
                'name' => 'Umbu',
                'hook' => self::pick(
                    'Ia membongkar radio rusak untuk belajar elektronika. Kini ia di kelas akhir SMA.',
                    'He took apart broken radios to teach himself electronics. He is now in his final year.'
                ),
                'image' => PlaceholderImage::make(800, 1000, self::pick(
                    'Foto: potret lingkungan Umbu',
                    'Photograph: environmental portrait of Umbu'
                )),
                'published_at' => '2026-05-20',
            ],
        ];
    }
}
