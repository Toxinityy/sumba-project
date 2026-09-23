<?php

namespace Database\Seeders;

use App\Models\Enums\PostKind;
use App\Models\Post;
use Illuminate\Database\Seeder;

/*
 | The site-wide stories and photo essays, copied from the fixture they
 | replace (app/ViewModels/PostData.php) so the rendered pages stay identical
 | and reviewable.
 |
 | THE OVERLAP THAT MATTERS: SchoolSeeder already creates a profile post per
 | person in a school's People section, and Karuni's head teacher is one of
 | them. She is also the voice that closes the landing page. One person is one
 | record — so her post is UPDATED here with the story's copy, not created a
 | second time. Two records would mean her portrait and her quote drifting
 | apart the first time someone edits one of them.
 |
 | The other two subjects are not in any People section, and are deliberately
 | left unattached (`about` stays null): attaching a story to a school would
 | add its subject to that school's People section, which is a page change
 | nobody asked for.
 */
class PostSeeder extends Seeder
{
    public function run(): void
    {
        foreach (self::stories() as $story) {
            $media = $story['media'];
            $match = $story['match'] ?? null;

            // An adult subject's surname is a row in subject_surnames now, not
            // a column on posts (spec §9), so it comes out of the attribute
            // array the same way media and match do.
            $surname = $story['subject_family_name'] ?? null;
            unset($story['media'], $story['match'], $story['subject_family_name']);

            // Match on the subject's stored name rather than the slug: the
            // school seeder slugged her "maria-bulu", the story is
            // "ibu-maria-bulu", and the slug is exactly what this changes.
            $post = $match === null ? null : Post::query()
                ->where('subject_given_name', $match[0])
                ->whereHas('subjectSurname', fn ($query) => $query
                    ->where('family_name', $match[1]))
                ->first();

            if ($post === null) {
                $post = Post::create($story);
            } else {
                $post->update($story);
            }

            // updateOrCreate so a reseed does not duplicate the row; the
            // database would refuse it anyway, since post_id is unique.
            if (filled($surname)) {
                $post->subjectSurname()->updateOrCreate([], ['family_name' => $surname]);
            }

            if ($post->media()->count() === 0) {
                $post->media()->create([
                    'path' => 'placeholder/'.$media['role'].'.jpg',
                    'width' => $media['width'],
                    'height' => $media['height'],
                    'alt' => $media['alt'],
                    'role' => $media['role'],
                ]);
            }
        }
    }

    /** @return array<int, array> */
    private static function stories(): array
    {
        return [
            [
                'slug' => ['id' => 'rambu-sembilan-kilometer', 'en' => 'rambu-sembilan-kilometer'],
                'kind' => PostKind::Profile,
                'title' => [
                    'id' => 'Sembilan kilometer, setiap pagi.',
                    'en' => 'Nine kilometres, every morning.',
                ],
                'hook' => [
                    'id' => 'Berjalan sembilan kilometer setiap pagi — sekarang ia mengajar adik kelasnya membaca.',
                    'en' => 'She walked nine kilometres each morning — now she teaches the younger pupils to read.',
                ],
                'body' => [
                    'id' => '<p>Rambu berangkat pukul lima pagi. Jalan dari kampungnya menurun melewati padang, menyeberangi satu sungai kecil yang meluap di musim hujan, lalu naik lagi ke jalan beraspal tempat sekolahnya berada. Sembilan kilometer. Ia melakukannya selama tiga tahun.</p><p>Tahun ini ia mengajar membaca untuk murid kelas satu dua kali seminggu, sebelum jam pelajarannya sendiri dimulai. Gurunya bilang ia menjelaskan lebih sabar daripada sebagian orang dewasa.</p>',
                    'en' => '<p>Rambu leaves at five in the morning. The path from her hamlet drops down across the grassland, crosses a small river that floods in the wet season, then climbs back up to the paved road where her school stands. Nine kilometres. She has done it for three years.</p><p>This year she teaches reading to the first-year pupils twice a week, before her own lessons begin. Her teacher says she explains things more patiently than some adults do.</p>',
                ],
                'quote' => [
                    'id' => '“Saya ingin jadi guru. Bukan di kota — di sini.”',
                    'en' => '“I want to be a teacher. Not in the city — here.”',
                ],
                // A pupil: given name only, no surname, no honorific (§9).
                'subject_given_name' => 'Rambu',
                'subject_is_minor' => true,
                'subject_role' => [
                    'id' => 'Kelas akhir, SMA Harapan Kambera',
                    'en' => 'Final year, SMA Harapan Kambera',
                ],
                'published_at' => '2026-08-14',
                'media' => [
                    'role' => 'portrait', 'width' => 800, 'height' => 1000,
                    'alt' => [
                        'id' => 'Foto: potret lingkungan Rambu',
                        'en' => 'Photograph: environmental portrait of Rambu',
                    ],
                ],
            ],
            [
                // Karuni's head teacher, already seeded as one of that
                // school's People. Matched and updated, never duplicated.
                'match' => ['Maria', 'Bulu'],
                'slug' => ['id' => 'ibu-maria-bulu', 'en' => 'ibu-maria-bulu'],
                'kind' => PostKind::Profile,
                'title' => [
                    'id' => 'Sebelas tahun merantau, lalu pulang untuk mengajar.',
                    'en' => 'Eleven years away, then home to teach.',
                ],
                'hook' => [
                    'id' => 'Kepala sekolah yang kembali ke desanya setelah sebelas tahun merantau.',
                    'en' => 'A head teacher who came back to her village after eleven years away.',
                ],
                'body' => [
                    'id' => '<p>Ibu Maria mengajar di Kupang selama sebelas tahun sebelum sebuah surat dari desanya sendiri mengubah rencananya: TK satu-satunya di Karuni kehilangan kepala sekolahnya, dan tidak ada pengganti.</p><p>Ia pulang tahun 2019. Sejak itu jumlah murid TK Harapan Karuni naik dari dua puluh menjadi enam puluh, dan ruang ketiga yang sedang dibangun adalah idenya sendiri.</p>',
                    'en' => '<p>Ibu Maria taught in Kupang for eleven years before a letter from her own village changed her plans: the only kindergarten in Karuni had lost its head teacher, with no replacement in sight.</p><p>She came home in 2019. Since then, enrolment at TK Harapan Karuni has grown from twenty pupils to sixty, and the third room now under construction was her own idea.</p>',
                ],
                'quote' => [
                    'id' => '“Anak-anak di sini sama pintarnya. Mereka hanya butuh ruang.”',
                    'en' => '“The children here are just as capable. They only need the room.”',
                ],
                'subject_given_name' => 'Maria',
                'subject_family_name' => 'Bulu',
                'subject_honorific' => 'Ibu',
                'subject_is_minor' => false,
                'subject_role' => [
                    'id' => 'Kepala sekolah, TK Harapan Karuni',
                    'en' => 'Head teacher, TK Harapan Karuni',
                ],
                'published_at' => '2026-07-02',
                'media' => [
                    'role' => 'portrait', 'width' => 800, 'height' => 1000,
                    'alt' => [
                        'id' => 'Foto: potret lingkungan Ibu Maria Bulu',
                        'en' => 'Photograph: environmental portrait of Ibu Maria Bulu',
                    ],
                ],
            ],
            [
                'slug' => ['id' => 'umbu-elektronika', 'en' => 'umbu-elektronika'],
                'kind' => PostKind::Profile,
                'title' => [
                    'id' => 'Ia membongkar radio rusak untuk belajar sendiri.',
                    'en' => 'He took broken radios apart to teach himself.',
                ],
                'hook' => [
                    'id' => 'Ia membongkar radio rusak untuk belajar elektronika. Kini ia di kelas akhir SMA.',
                    'en' => 'He took apart broken radios to teach himself electronics. He is now in his final year.',
                ],
                'body' => [
                    'id' => '<p>Umbu mulai membongkar radio rusak milik tetangganya sejak kelas lima, hanya untuk melihat bagaimana bagian dalamnya bekerja. Tidak ada kelas elektronika di sekolahnya waktu itu — ia belajar dari mencoba dan gagal berulang kali.</p><p>Sekarang, di kelas akhir, ia salah satu dari dua belas murid yang memenuhi syarat masuk universitas tahun ini. Ia berharap bisa belajar teknik.</p>',
                    'en' => "<p>Umbu started taking apart his neighbours' broken radios in fifth grade, just to see how the parts fit together. There was no electronics class at his school back then — he learned by trying and failing, repeatedly.</p><p>Now, in his final year, he is one of twelve pupils who qualify for university entry this year. He hopes to study engineering.</p>",
                ],
                'quote' => [
                    'id' => '“Kalau bisa dibongkar, bisa dipahami.”',
                    'en' => '“If it can be taken apart, it can be understood.”',
                ],
                'subject_given_name' => 'Umbu',
                'subject_is_minor' => true,
                'subject_role' => [
                    'id' => 'Kelas akhir, SMA Harapan Waikabubak',
                    'en' => 'Final year, SMA Harapan Waikabubak',
                ],
                'published_at' => '2026-05-20',
                'media' => [
                    'role' => 'portrait', 'width' => 800, 'height' => 1000,
                    'alt' => [
                        'id' => 'Foto: potret lingkungan Umbu',
                        'en' => 'Photograph: environmental portrait of Umbu',
                    ],
                ],
            ],
            [
                'slug' => ['id' => 'panen-raya-karuni', 'en' => 'panen-raya-karuni'],
                'kind' => PostKind::PhotoEssay,
                'title' => [
                    'id' => 'Panen bersama di Karuni',
                    'en' => 'A shared harvest in Karuni',
                ],
                'hook' => [
                    'id' => 'Orang tua dan guru bekerja sama menyiapkan tanah untuk kebun sekolah.',
                    'en' => 'Parents and teachers working the ground together for the school garden.',
                ],
                'published_at' => '2026-06-10',
                'media' => [
                    'role' => 'essay', 'width' => 1200, 'height' => 800,
                    'alt' => [
                        'id' => 'Foto: esai foto — warga desa menyiapkan lahan kebun sekolah',
                        'en' => 'Photograph: photo essay — villagers preparing the school garden plot',
                    ],
                ],
            ],
            [
                'slug' => ['id' => 'hari-pertama-sekolah', 'en' => 'hari-pertama-sekolah'],
                'kind' => PostKind::PhotoEssay,
                'title' => [
                    'id' => 'Hari pertama tahun ajaran baru',
                    'en' => 'The first day of the new school year',
                ],
                'hook' => [
                    'id' => 'Seragam baru, buku baru, dan halaman sekolah yang ramai lagi.',
                    'en' => 'New uniforms, new books, and a school yard full again.',
                ],
                'published_at' => '2026-07-15',
                'media' => [
                    'role' => 'essay', 'width' => 1200, 'height' => 800,
                    'alt' => [
                        'id' => 'Foto: esai foto — murid berkumpul di halaman pada hari pertama sekolah',
                        'en' => 'Photograph: photo essay — pupils gathering in the yard on the first day of school',
                    ],
                ],
            ],
        ];
    }
}
