<?php

namespace Database\Seeders;

use App\Models\Enums\PostKind;
use App\Models\Enums\ProjectStatus;
use App\Models\School;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/*
 | The six schools, copied from App\ViewModels\SchoolData so the pages render
 | identically from the database as they did from the fixture — that is how a
 | reviewer can tell the switch changed nothing. The copy is literal rather
 | than read from the fixture at runtime, because the fixture is deleted once
 | the pages move over.
 |
 | Placeholder content throughout, as in the fixture: invented, labelled, and
 | to be replaced by the team's own before launch. The teachers are adults and
 | named in full; no child is named or pictured here.
 */
class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        foreach (self::schools() as $data) {
            $school = School::create([
                ...collect($data)->except(['media', 'evidence', 'people'])->all(),
                'published_at' => now(),
            ]);

            foreach ($data['media'] as $role => $image) {
                self::attach($school, $role, $image);
            }

            // The Evidence pair belongs to the project, not the school: the
            // work shown is the work the school's current need describes.
            $project = $school->projects()->create([
                'slug' => ['id' => $data['slug']['id'].'-proyek', 'en' => $data['slug']['en'].'-project'],
                'title' => $data['work_heading'],
                'status' => ProjectStatus::Underway,
                'published_at' => now(),
            ]);

            foreach ($data['evidence'] as $role => $image) {
                self::attach($project, $role, $image);
            }

            foreach ($data['people'] as $person) {
                [$given, $family] = explode(' ', $person['name'], 2) + [1 => null];

                $post = $school->posts()->create([
                    'slug' => ['id' => Str::slug($person['name']), 'en' => Str::slug($person['name'])],
                    'title' => ['id' => $person['name'], 'en' => $person['name']],
                    'kind' => PostKind::Profile,
                    'subject_given_name' => $given,
                    'subject_family_name' => $family,
                    'subject_is_minor' => false,
                    'published_at' => now(),
                ]);

                self::attach($post, 'portrait', $person);
            }
        }
    }

    private static function attach(Model $owner, string $role, array $image): void
    {
        $owner->media()->create([
            'path' => 'placeholder/'.$role.'.jpg',
            'width' => $image['width'],
            'height' => $image['height'],
            'alt' => $image['alt'],
            'caption' => $image['caption'] ?? null,
            'role' => $role,
        ]);
    }

    private static function schools(): array
    {
        return [
            [
                'slug' => [
                    'id' => 'karuni',
                    'en' => 'karuni',
                ],
                'level' => 'TK',
                'name' => [
                    'id' => 'TK Harapan Karuni',
                    'en' => 'TK Harapan Karuni',
                ],
                'location' => [
                    'id' => 'Karuni, Sumba Barat Daya',
                    'en' => 'Karuni, Sumba Barat Daya',
                ],
                'current_need' => [
                    'id' => 'Ruang baca baru untuk 60 anak.',
                    'en' => 'A new reading room for 60 children.',
                ],
                'status' => [
                    'id' => 'Butuh 4 mitra lagi',
                    'en' => 'Needs 4 more partners',
                ],
                'lede' => [
                    'id' => 'Enam puluh anak belajar di dua ruang kelas. Ruang ketiga akan menjadi perpustakaan pertama di desa ini — tempat anak-anak bisa membaca setelah jam sekolah, dan tempat orang tua belajar membaca bersama mereka.',
                    'en' => 'Sixty children learn in two classrooms. A third room will become the village\'s first library — somewhere children can read after school, and where parents learn to read alongside them.',
                ],
                'context_heading' => [
                    'id' => 'Tidak ada tempat membaca setelah pulang sekolah.',
                    'en' => 'There is nowhere to read after school.',
                ],
                'context_body' => [
                    'id' => 'Di Karuni belum ada perpustakaan, dan sebagian besar rumah belum punya aliran listrik untuk membaca setelah gelap. Buku yang ada disimpan di lemari ruang guru dan hanya bisa dipakai saat jam pelajaran.',
                    'en' => 'Karuni has no library, and most homes have no electricity to read by after dark. The books the school owns are kept in a cupboard in the staff room and can only be used during lessons.',
                ],
                'work_heading' => [
                    'id' => 'Ruang ketiga menjadi perpustakaan.',
                    'en' => 'The third room becomes a library.',
                ],
                'work_body' => [
                    'id' => 'Pekerjaan bangunan hampir selesai. Yang belum tersedia adalah rak untuk sisi kedua ruangan, koleksi buku berbahasa Indonesia untuk usia dini, dan satu panel surya kecil agar ruangan bisa dipakai sampai malam.',
                    'en' => 'The building work is nearly done. What is still missing is shelving for the second side of the room, a collection of early-years books in Indonesian, and one small solar panel so the room can be used into the evening.',
                ],
                'opened_year' => 2009,
                'pupils' => 60,
                'teachers' => 3,
                'media' => [
                    'hero' => [
                        'width' => 800,
                        'height' => 1000,
                        'alt' => [
                            'id' => 'Foto: murid-murid TK Harapan Karuni sedang belajar',
                            'en' => 'Photograph: pupils at TK Harapan Karuni in class',
                        ],
                    ],
                    'context' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: lemari buku di ruang guru, sore hari',
                            'en' => 'Photograph: the book cupboard in the staff room, late afternoon',
                        ],
                    ],
                    'work' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: rangka atap dan rak pertama terpasang',
                            'en' => 'Photograph: roof frame and the first shelving installed',
                        ],
                    ],
                ],
                'evidence' => [
                    'before' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: ruang ketiga sebelum dikerjakan',
                            'en' => 'Photograph: the third room before work began',
                        ],
                        'caption' => [
                            'id' => 'Maret 2026 — ruang ketiga, belum terpakai',
                            'en' => 'March 2026 — the third room, unused',
                        ],
                    ],
                    'after' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: rangka atap dan rak pertama terpasang',
                            'en' => 'Photograph: roof frame and the first shelving installed',
                        ],
                        'caption' => [
                            'id' => 'Agustus 2026 — atap dan rak pertama terpasang',
                            'en' => 'August 2026 — roof and first shelving in place',
                        ],
                    ],
                ],
                'people' => [
                    [
                        'name' => 'Maria Bulu',
                        'width' => 800,
                        'height' => 1000,
                        'alt' => [
                            'id' => 'Potret Ibu Maria Bulu di ruang kelas',
                            'en' => 'Portrait of Ibu Maria Bulu in her classroom',
                        ],
                    ],
                    [
                        'name' => 'Yuliana Ndapa',
                        'width' => 800,
                        'height' => 1000,
                        'alt' => [
                            'id' => 'Potret Ibu Yuliana Ndapa di ruang kelas',
                            'en' => 'Portrait of Ibu Yuliana Ndapa in her classroom',
                        ],
                    ],
                    [
                        'name' => 'Yosef Praing',
                        'width' => 800,
                        'height' => 1000,
                        'alt' => [
                            'id' => 'Potret Bapak Yosef Praing di halaman sekolah',
                            'en' => 'Portrait of Bapak Yosef Praing in the school yard',
                        ],
                    ],
                ],
            ],
            [
                'slug' => [
                    'id' => 'anakalang',
                    'en' => 'anakalang',
                ],
                'level' => 'SMP',
                'name' => [
                    'id' => 'SMP Harapan Anakalang',
                    'en' => 'SMP Harapan Anakalang',
                ],
                'location' => [
                    'id' => 'Anakalang, Sumba Tengah',
                    'en' => 'Anakalang, Sumba Tengah',
                ],
                'current_need' => [
                    'id' => 'Guru IPA untuk tahun ajaran baru.',
                    'en' => 'A science teacher for the new school year.',
                ],
                'status' => [
                    'id' => 'Sedang mencari mitra pendidik',
                    'en' => 'Seeking a teaching partner',
                ],
                'lede' => [
                    'id' => 'Seratus sepuluh murid belajar IPA dari buku teks saja sejak guru tetapnya pindah tugas awal tahun ini. Kepala sekolah mengajar sendiri di sela jam mengajarnya yang lain, tetapi itu bukan solusi jangka panjang.',
                    'en' => 'A hundred and ten pupils have studied science from the textbook alone since the school\'s permanent teacher was transferred at the start of this year. The head teacher covers the classes between her own lessons, but that is not a lasting fix.',
                ],
                'context_heading' => [
                    'id' => 'Anakalang tidak ada di jalur mengajar guru IPA bersertifikat.',
                    'en' => 'Anakalang sits off the route qualified science teachers post to.',
                ],
                'context_body' => [
                    'id' => 'Sekolah menengah terdekat dengan guru IPA tetap berjarak dua jam berkendara. Pemerintah daerah menawarkan formasi, tetapi belum ada pelamar sejak posisi ini kosong tahun ini.',
                    'en' => 'The nearest secondary school with a permanent science teacher is a two-hour drive away. The local government has an open post, but no applicant has taken it since the vacancy opened this year.',
                ],
                'work_heading' => [
                    'id' => 'Mengisi kekosongan sampai formasi terisi.',
                    'en' => 'Bridging the gap until the post is filled.',
                ],
                'work_body' => [
                    'id' => 'Kami sedang mencari mitra pendidik yang bisa mendanai satu guru IPA kontrak untuk tahun ajaran ini, sambil pemerintah daerah terus mencari kandidat tetap.',
                    'en' => 'We\'re looking for an education partner able to fund one contract science teacher for this school year, while the local government continues its search for a permanent candidate.',
                ],
                'opened_year' => 2012,
                'pupils' => 110,
                'teachers' => 6,
                'media' => [
                    'hero' => [
                        'width' => 800,
                        'height' => 1000,
                        'alt' => [
                            'id' => 'Foto: murid-murid SMP Harapan Anakalang sedang belajar',
                            'en' => 'Photograph: pupils at SMP Harapan Anakalang in class',
                        ],
                    ],
                    'context' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: laboratorium IPA sederhana, tanpa guru tetap',
                            'en' => 'Photograph: the modest science room, without a permanent teacher',
                        ],
                    ],
                    'work' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: murid-murid mengerjakan latihan IPA dari buku teks',
                            'en' => 'Photograph: pupils working through a science exercise from the textbook',
                        ],
                    ],
                ],
                'evidence' => [
                    'before' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: ruang IPA kosong, awal tahun ajaran',
                            'en' => 'Photograph: the empty science room, start of the school year',
                        ],
                        'caption' => [
                            'id' => 'Januari 2026 — ruang IPA tanpa guru tetap',
                            'en' => 'January 2026 — the science room without a permanent teacher',
                        ],
                    ],
                    'after' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: kepala sekolah mengajar kelas IPA sementara',
                            'en' => 'Photograph: the head teacher covering a science lesson',
                        ],
                        'caption' => [
                            'id' => 'Agustus 2026 — kelas berjalan, diajar bergantian',
                            'en' => 'August 2026 — lessons continuing, covered on rotation',
                        ],
                    ],
                ],
                'people' => [
                    [
                        'name' => 'Ana Kolimon',
                        'width' => 800,
                        'height' => 1000,
                        'alt' => [
                            'id' => 'Potret Ibu Ana Kolimon di ruang kelas',
                            'en' => 'Portrait of Ibu Ana Kolimon in her classroom',
                        ],
                    ],
                    [
                        'name' => 'Daniel Awang',
                        'width' => 800,
                        'height' => 1000,
                        'alt' => [
                            'id' => 'Potret Bapak Daniel Awang di halaman sekolah',
                            'en' => 'Portrait of Bapak Daniel Awang in the school yard',
                        ],
                    ],
                ],
            ],
            [
                'slug' => [
                    'id' => 'kambera',
                    'en' => 'kambera',
                ],
                'level' => 'SMA',
                'name' => [
                    'id' => 'SMA Harapan Kambera',
                    'en' => 'SMA Harapan Kambera',
                ],
                'location' => [
                    'id' => 'Kambera, Sumba Timur',
                    'en' => 'Kambera, Sumba Timur',
                ],
                'current_need' => [
                    'id' => 'Pembaruan laboratorium komputer.',
                    'en' => 'Computer laboratory refurbishment.',
                ],
                'status' => [
                    'id' => 'Perlu 2 mitra korporasi',
                    'en' => 'Needs 2 corporate partners',
                ],
                'lede' => [
                    'id' => 'Dua puluh dua komputer melayani seratus delapan puluh murid kelas akhir yang membutuhkannya untuk ujian berbasis komputer. Delapan di antaranya sudah tidak menyala, dan yang tersisa berbagi satu jaringan listrik yang sering padam.',
                    'en' => 'Twenty-two computers serve a hundred and eighty final-year pupils who need them for computer-based exams. Eight no longer switch on, and the rest share one power circuit that regularly cuts out.',
                ],
                'context_heading' => [
                    'id' => 'Ujian berbasis komputer bukan pilihan lagi.',
                    'en' => 'Computer-based exams are no longer optional.',
                ],
                'context_body' => [
                    'id' => 'Pemerintah mewajibkan ujian akhir berbasis komputer, tetapi laboratorium sekolah dibeli tahun 2014 dan tidak pernah diperbarui. Murid bergiliran dalam sesi ujian yang jauh lebih panjang dari seharusnya.',
                    'en' => 'The government now requires final exams to run on computers, but the school\'s lab was bought in 2014 and never upgraded. Pupils sit exams in shifts that run far longer than they should.',
                ],
                'work_heading' => [
                    'id' => 'Memperbarui, bukan mengganti semuanya sekaligus.',
                    'en' => 'Refurbishing, not replacing everything at once.',
                ],
                'work_body' => [
                    'id' => 'Rencananya bertahap: memperbaiki jaringan listrik terlebih dahulu, lalu mengganti unit yang benar-benar rusak setiap semester sampai laboratorium penuh berfungsi lagi.',
                    'en' => 'The plan is staged: fix the electrical circuit first, then replace the units that are beyond repair one term at a time until the lab is fully working again.',
                ],
                'opened_year' => 2005,
                'pupils' => 180,
                'teachers' => 14,
                'media' => [
                    'hero' => [
                        'width' => 800,
                        'height' => 1000,
                        'alt' => [
                            'id' => 'Foto: murid-murid SMA Harapan Kambera sedang belajar',
                            'en' => 'Photograph: pupils at SMA Harapan Kambera in class',
                        ],
                    ],
                    'context' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: baris komputer tua di laboratorium, beberapa mati',
                            'en' => 'Photograph: a row of ageing computers in the lab, several switched off',
                        ],
                    ],
                    'work' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: teknisi memeriksa kabel jaringan listrik laboratorium',
                            'en' => 'Photograph: a technician checking the lab\'s wiring',
                        ],
                    ],
                ],
                'evidence' => [
                    'before' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: laboratorium komputer dengan unit yang tidak menyala',
                            'en' => 'Photograph: the computer lab with units that will not switch on',
                        ],
                        'caption' => [
                            'id' => 'April 2026 — delapan unit tidak berfungsi',
                            'en' => 'April 2026 — eight units out of service',
                        ],
                    ],
                    'after' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: jaringan listrik laboratorium yang telah diperbaiki',
                            'en' => 'Photograph: the lab\'s repaired electrical circuit',
                        ],
                        'caption' => [
                            'id' => 'Agustus 2026 — jaringan listrik selesai diperbaiki',
                            'en' => 'August 2026 — the electrical circuit repair complete',
                        ],
                    ],
                ],
                'people' => [
                    [
                        'name' => 'Umbu Ratu Djima',
                        'width' => 800,
                        'height' => 1000,
                        'alt' => [
                            'id' => 'Potret Bapak Umbu Ratu Djima di laboratorium komputer',
                            'en' => 'Portrait of Bapak Umbu Ratu Djima in the computer lab',
                        ],
                    ],
                    [
                        'name' => 'Ruth Malo',
                        'width' => 800,
                        'height' => 1000,
                        'alt' => [
                            'id' => 'Potret Ibu Ruth Malo di ruang guru',
                            'en' => 'Portrait of Ibu Ruth Malo in the staff room',
                        ],
                    ],
                ],
            ],
            [
                'slug' => [
                    'id' => 'melolo',
                    'en' => 'melolo',
                ],
                'level' => 'TK',
                'name' => [
                    'id' => 'TK Harapan Melolo',
                    'en' => 'TK Harapan Melolo',
                ],
                'location' => [
                    'id' => 'Melolo, Sumba Timur',
                    'en' => 'Melolo, Sumba Timur',
                ],
                'current_need' => [
                    'id' => 'Perlengkapan bermain dan belajar.',
                    'en' => 'Play and learning equipment.',
                ],
                'status' => [
                    'id' => 'Didanai penuh tahun ini',
                    'en' => 'Fully funded this year',
                ],
                'lede' => [
                    'id' => 'Delapan puluh anak usia dini bermain dan belajar di satu ruang terbuka yang dibagi dengan tikar dan rak rendah. Tahun lalu perlengkapan bermainnya sudah usang; tahun ini, berkat satu mitra jangka panjang, semuanya baru.',
                    'en' => 'Eighty young children play and learn in one open room divided by mats and low shelving. Last year the play equipment was worn through; this year, thanks to one long-term partner, all of it is new.',
                ],
                'context_heading' => [
                    'id' => 'Perlengkapan yang sudah dipakai sejak sekolah dibuka.',
                    'en' => 'Equipment that has been in use since the school opened.',
                ],
                'context_body' => [
                    'id' => 'Balok kayu, puzzle dan alat menggambar sebagian besar berasal dari sumbangan awal tahun 2011 dan sudah retak atau hilang bagiannya. Guru menambal dengan bahan seadanya agar anak-anak tetap punya sesuatu untuk dipegang.',
                    'en' => 'Wooden blocks, puzzles and drawing materials mostly date from the school\'s opening donation in 2011 and are cracked or missing pieces. Teachers patch what they can so the children still have something to hold.',
                ],
                'work_heading' => [
                    'id' => 'Satu mitra menutup seluruh kebutuhan tahun ini.',
                    'en' => 'One partner covered the full need this year.',
                ],
                'work_body' => [
                    'id' => 'Sebuah yayasan mitra mendanai penggantian penuh perlengkapan bermain dan belajar untuk tahun ajaran ini — balok baru, buku bergambar, dan alat menggambar untuk delapan puluh anak.',
                    'en' => 'A partner foundation funded a full replacement of the play and learning equipment for this school year — new blocks, picture books and drawing materials for eighty children.',
                ],
                'opened_year' => 2011,
                'pupils' => 80,
                'teachers' => 4,
                'media' => [
                    'hero' => [
                        'width' => 800,
                        'height' => 1000,
                        'alt' => [
                            'id' => 'Foto: murid-murid TK Harapan Melolo sedang bermain',
                            'en' => 'Photograph: pupils at TK Harapan Melolo at play',
                        ],
                    ],
                    'context' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: rak perlengkapan bermain yang sudah usang',
                            'en' => 'Photograph: the shelf of worn play equipment',
                        ],
                    ],
                    'work' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: perlengkapan bermain baru tersusun di rak',
                            'en' => 'Photograph: new play equipment arranged on the shelf',
                        ],
                    ],
                ],
                'evidence' => [
                    'before' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: perlengkapan bermain lama dan retak',
                            'en' => 'Photograph: the old, cracked play equipment',
                        ],
                        'caption' => [
                            'id' => 'Februari 2026 — perlengkapan lama masih dipakai',
                            'en' => 'February 2026 — the old equipment still in use',
                        ],
                    ],
                    'after' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: anak-anak bermain dengan perlengkapan baru',
                            'en' => 'Photograph: children playing with the new equipment',
                        ],
                        'caption' => [
                            'id' => 'Agustus 2026 — perlengkapan baru terpasang penuh',
                            'en' => 'August 2026 — the new equipment fully in place',
                        ],
                    ],
                ],
                'people' => [
                    [
                        'name' => 'Dorkas Lende',
                        'width' => 800,
                        'height' => 1000,
                        'alt' => [
                            'id' => 'Potret Ibu Dorkas Lende di ruang bermain',
                            'en' => 'Portrait of Ibu Dorkas Lende in the play room',
                        ],
                    ],
                    [
                        'name' => 'Naomi Rambu',
                        'width' => 800,
                        'height' => 1000,
                        'alt' => [
                            'id' => 'Potret Ibu Naomi Rambu di halaman TK',
                            'en' => 'Portrait of Ibu Naomi Rambu in the kindergarten yard',
                        ],
                    ],
                ],
            ],
            [
                'slug' => [
                    'id' => 'lewa',
                    'en' => 'lewa',
                ],
                'level' => 'SMP',
                'name' => [
                    'id' => 'SMP Harapan Lewa',
                    'en' => 'SMP Harapan Lewa',
                ],
                'location' => [
                    'id' => 'Lewa, Sumba Timur',
                    'en' => 'Lewa, Sumba Timur',
                ],
                'current_need' => [
                    'id' => 'Asrama putri untuk murid dari desa jauh.',
                    'en' => 'A girls\' dormitory for pupils from distant villages.',
                ],
                'status' => [
                    'id' => 'Pembangunan sedang berjalan',
                    'en' => 'Construction underway',
                ],
                'lede' => [
                    'id' => 'Sepertiga murid perempuan di sekolah ini berjalan lebih dari dua jam setiap hari dari desa-desa di perbukitan. Sebagian berhenti sekolah begitu musim hujan membuat jalan itu tidak bisa dilewati.',
                    'en' => 'A third of this school\'s girls walk more than two hours each day from villages in the hills. Some stop attending once the wet season makes that walk impassable.',
                ],
                'context_heading' => [
                    'id' => 'Jarak, bukan minat belajar, yang menghentikan mereka.',
                    'en' => 'Distance, not the will to learn, is what stops them.',
                ],
                'context_body' => [
                    'id' => 'Jalan dari desa-desa terjauh menyeberangi dua sungai musiman. Saat air naik, murid perempuan yang biasanya berjalan bersama rombongan memilih tinggal di rumah daripada menyeberang sendiri.',
                    'en' => 'The road from the furthest villages crosses two seasonal rivers. When the water rises, girls who normally walk in a group choose to stay home rather than cross alone.',
                ],
                'work_heading' => [
                    'id' => 'Membangun tempat tinggal, bukan hanya jalan pintas.',
                    'en' => 'Building somewhere to stay, not just a shortcut.',
                ],
                'work_body' => [
                    'id' => 'Pondasi asrama putri untuk dua puluh empat tempat tidur sudah selesai. Dinding dan atap sedang dikerjakan bertahap oleh tukang setempat, mengikuti dana yang masuk setiap bulan.',
                    'en' => 'The foundation for a twenty-four-bed girls\' dormitory is complete. Walls and roofing are being built in stages by local builders, following the funds that come in each month.',
                ],
                'opened_year' => 2008,
                'pupils' => 145,
                'teachers' => 9,
                'media' => [
                    'hero' => [
                        'width' => 800,
                        'height' => 1000,
                        'alt' => [
                            'id' => 'Foto: murid-murid SMP Harapan Lewa sedang belajar',
                            'en' => 'Photograph: pupils at SMP Harapan Lewa in class',
                        ],
                    ],
                    'context' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: jalan setapak menuju sekolah melintasi sungai musiman',
                            'en' => 'Photograph: the footpath to school crossing a seasonal river',
                        ],
                    ],
                    'work' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: pondasi asrama putri yang sudah selesai',
                            'en' => 'Photograph: the completed dormitory foundation',
                        ],
                    ],
                ],
                'evidence' => [
                    'before' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: lahan kosong sebelum pembangunan asrama',
                            'en' => 'Photograph: the empty plot before dormitory construction',
                        ],
                        'caption' => [
                            'id' => 'Januari 2026 — lahan sebelum digali',
                            'en' => 'January 2026 — the plot before groundwork began',
                        ],
                    ],
                    'after' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: pondasi asrama putri yang sudah selesai',
                            'en' => 'Photograph: the completed dormitory foundation',
                        ],
                        'caption' => [
                            'id' => 'Agustus 2026 — pondasi selesai, dinding mulai dikerjakan',
                            'en' => 'August 2026 — foundation complete, walls underway',
                        ],
                    ],
                ],
                'people' => [
                    [
                        'name' => 'Melkiana Tamu Ina',
                        'width' => 800,
                        'height' => 1000,
                        'alt' => [
                            'id' => 'Potret Ibu Melkiana Tamu Ina di depan pondasi asrama',
                            'en' => 'Portrait of Ibu Melkiana Tamu Ina at the dormitory foundation',
                        ],
                    ],
                    [
                        'name' => 'Yoram Dapa Loka',
                        'width' => 800,
                        'height' => 1000,
                        'alt' => [
                            'id' => 'Potret Bapak Yoram Dapa Loka di halaman sekolah',
                            'en' => 'Portrait of Bapak Yoram Dapa Loka in the school yard',
                        ],
                    ],
                ],
            ],
            [
                'slug' => [
                    'id' => 'waikabubak',
                    'en' => 'waikabubak',
                ],
                'level' => 'SMA',
                'name' => [
                    'id' => 'SMA Harapan Waikabubak',
                    'en' => 'SMA Harapan Waikabubak',
                ],
                'location' => [
                    'id' => 'Waikabubak, Sumba Barat',
                    'en' => 'Waikabubak, Sumba Barat',
                ],
                'current_need' => [
                    'id' => 'Beasiswa kelas akhir untuk 12 murid.',
                    'en' => 'Final-year scholarships for 12 pupils.',
                ],
                'status' => [
                    'id' => 'Butuh 5 mitra lagi',
                    'en' => 'Needs 5 more partners',
                ],
                'lede' => [
                    'id' => 'Dua belas murid kelas akhir memenuhi syarat masuk universitas tahun ini, tetapi enam di antaranya berisiko putus sebelum ujian akhir karena orang tua mereka tidak lagi mampu membayar biaya transportasi dan buku ujian.',
                    'en' => 'Twelve final-year pupils qualify for university entry this year, but six of them risk dropping out before finals because their parents can no longer cover transport and exam-book costs.',
                ],
                'context_heading' => [
                    'id' => 'Lulus ujian bukan jaminan bisa mengikutinya.',
                    'en' => 'Qualifying for the exam is not the same as being able to sit it.',
                ],
                'context_body' => [
                    'id' => 'Ujian akhir diadakan di kota kabupaten, dua jam dari rumah sebagian besar murid. Ongkos transportasi dan buku persiapan ujian setara dengan upah sebulan orang tua mereka.',
                    'en' => 'Finals are held in the district town, two hours from most pupils\' homes. Transport and exam-preparation books together cost about a month of their parents\' wages.',
                ],
                'work_heading' => [
                    'id' => 'Beasiswa yang menutup biaya, bukan uang saku.',
                    'en' => 'A scholarship that covers costs, not pocket money.',
                ],
                'work_body' => [
                    'id' => 'Setiap beasiswa menanggung ongkos transportasi ke tempat ujian, buku persiapan, dan biaya pendaftaran — dibayarkan langsung ke sekolah, bukan ke murid.',
                    'en' => 'Each scholarship covers transport to the exam site, preparation books, and the registration fee — paid directly to the school, not to the pupil.',
                ],
                'opened_year' => 2003,
                'pupils' => 210,
                'teachers' => 16,
                'media' => [
                    'hero' => [
                        'width' => 800,
                        'height' => 1000,
                        'alt' => [
                            'id' => 'Foto: murid-murid SMA Harapan Waikabubak sedang belajar',
                            'en' => 'Photograph: pupils at SMA Harapan Waikabubak in class',
                        ],
                    ],
                    'context' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: murid kelas akhir belajar bersama menjelang ujian',
                            'en' => 'Photograph: final-year pupils studying together ahead of exams',
                        ],
                    ],
                    'work' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: murid menerima buku persiapan ujian dari guru',
                            'en' => 'Photograph: a pupil receiving exam-preparation books from a teacher',
                        ],
                    ],
                ],
                'evidence' => [
                    'before' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: daftar murid berisiko putus sebelum ujian akhir',
                            'en' => 'Photograph: the list of pupils at risk of dropping out before finals',
                        ],
                        'caption' => [
                            'id' => 'Juni 2026 — enam murid berisiko putus sekolah',
                            'en' => 'June 2026 — six pupils at risk of dropping out',
                        ],
                    ],
                    'after' => [
                        'width' => 1200,
                        'height' => 800,
                        'alt' => [
                            'id' => 'Foto: murid kelas akhir belajar bersama menjelang ujian',
                            'en' => 'Photograph: final-year pupils studying together ahead of exams',
                        ],
                        'caption' => [
                            'id' => 'Agustus 2026 — seluruh dua belas murid terdaftar ujian',
                            'en' => 'August 2026 — all twelve pupils registered for finals',
                        ],
                    ],
                ],
                'people' => [
                    [
                        'name' => 'Rambu Kahi',
                        'width' => 800,
                        'height' => 1000,
                        'alt' => [
                            'id' => 'Potret Ibu Rambu Kahi di ruang guru',
                            'en' => 'Portrait of Ibu Rambu Kahi in the staff room',
                        ],
                    ],
                    [
                        'name' => 'Markus Wadu',
                        'width' => 800,
                        'height' => 1000,
                        'alt' => [
                            'id' => 'Potret Bapak Markus Wadu di perpustakaan sekolah',
                            'en' => 'Portrait of Bapak Markus Wadu in the school library',
                        ],
                    ],
                ],
            ],
        ];
    }
}
