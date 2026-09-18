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
 * sekolah-karuni.html page body) where it exists. Only 'karuni' is
 * prototype-reviewed copy; the prototype itself only built one school
 * detail page. Pass 4 (2026-09-18) wrote full detail shapes for the other
 * five so every card in the directory leads somewhere real — invented
 * placeholder copy, not prototype-sourced, written in the same register
 * and kept distinct per school so the directory doesn't read as six copies
 * of one school. `status` stays a qualitative sentence for all six, never
 * a number, per the contract's rule 1 — including 'melolo', whose status
 * is "fully funded", a legitimate qualitative value, not an exception.
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
            'slug', 'href', 'level', 'age_range', 'name', 'location', 'need', 'status', 'image',
        ]));
    }

    /**
     * The age range the lit rung of the level ladder carries beneath it.
     *
     * Derived from `level` rather than stored per school so the two can never
     * disagree: a school whose level says SMP and whose age range says 4-6 is
     * a data bug a reader would spot before the team did. See
     * docs/landing-redesign-report.md — docs/data-contract.md's School shape
     * needs an `age_range` key so the data layer produces this for real.
     */
    private static function ageRange(string $level): string
    {
        return match ($level) {
            'TK' => self::pick('4-6 tahun', 'ages 4-6'),
            'SMP' => self::pick('12-15 tahun', 'ages 12-15'),
            'SMA' => self::pick('15-18 tahun', 'ages 15-18'),
        };
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
                'age_range' => self::ageRange('TK'),
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
                'age_range' => self::ageRange('SMP'),
                'name' => 'SMP Harapan Anakalang',
                'location' => 'Anakalang, Sumba Tengah',
                'need' => self::pick('Guru IPA untuk tahun ajaran baru.', 'A science teacher for the new school year.'),
                'status' => self::pick('Sedang mencari mitra pendidik', 'Seeking a teaching partner'),
                'image' => PlaceholderImage::make(800, 1000, self::pick(
                    'Foto: murid-murid SMP Harapan Anakalang sedang belajar',
                    'Photograph: pupils at SMP Harapan Anakalang in class'
                )),
                'lede' => self::pick(
                    'Seratus sepuluh murid belajar IPA dari buku teks saja sejak guru tetapnya pindah tugas awal tahun ini. Kepala sekolah mengajar sendiri di sela jam mengajarnya yang lain, tetapi itu bukan solusi jangka panjang.',
                    "A hundred and ten pupils have studied science from the textbook alone since the school's permanent teacher was transferred at the start of this year. The head teacher covers the classes between her own lessons, but that is not a lasting fix."
                ),
                'people' => [
                    PlaceholderImage::make(800, 1000, self::pick('Potret Ibu Ana Kolimon di ruang kelas', 'Portrait of Ibu Ana Kolimon in her classroom'))
                        + ['name' => 'Ana Kolimon'],
                    PlaceholderImage::make(800, 1000, self::pick('Potret Bapak Daniel Awang di halaman sekolah', 'Portrait of Bapak Daniel Awang in the school yard'))
                        + ['name' => 'Daniel Awang'],
                ],
                'context' => [
                    'heading' => self::pick(
                        'Anakalang tidak ada di jalur mengajar guru IPA bersertifikat.',
                        'Anakalang sits off the route qualified science teachers post to.'
                    ),
                    'body' => self::pick(
                        'Sekolah menengah terdekat dengan guru IPA tetap berjarak dua jam berkendara. Pemerintah daerah menawarkan formasi, tetapi belum ada pelamar sejak posisi ini kosong tahun ini.',
                        'The nearest secondary school with a permanent science teacher is a two-hour drive away. The local government has an open post, but no applicant has taken it since the vacancy opened this year.'
                    ),
                    'image' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: laboratorium IPA sederhana, tanpa guru tetap',
                        'Photograph: the modest science room, without a permanent teacher'
                    )),
                ],
                'work' => [
                    'heading' => self::pick(
                        'Mengisi kekosongan sampai formasi terisi.',
                        'Bridging the gap until the post is filled.'
                    ),
                    'body' => self::pick(
                        'Kami sedang mencari mitra pendidik yang bisa mendanai satu guru IPA kontrak untuk tahun ajaran ini, sambil pemerintah daerah terus mencari kandidat tetap.',
                        "We're looking for an education partner able to fund one contract science teacher for this school year, while the local government continues its search for a permanent candidate."
                    ),
                    'image' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: murid-murid mengerjakan latihan IPA dari buku teks',
                        'Photograph: pupils working through a science exercise from the textbook'
                    )),
                ],
                'evidence' => [
                    'before' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: ruang IPA kosong, awal tahun ajaran',
                        'Photograph: the empty science room, start of the school year'
                    )) + ['caption' => self::pick('Januari 2026 — ruang IPA tanpa guru tetap', 'January 2026 — the science room without a permanent teacher')],
                    'after' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: kepala sekolah mengajar kelas IPA sementara',
                        "Photograph: the head teacher covering a science lesson"
                    )) + ['caption' => self::pick('Agustus 2026 — kelas berjalan, diajar bergantian', 'August 2026 — lessons continuing, covered on rotation')],
                ],
                'facts' => [
                    ['key' => self::pick('Dibuka', 'Opened'), 'value' => '2012'],
                    ['key' => self::pick('Murid', 'Pupils'), 'value' => '110'],
                    ['key' => self::pick('Guru', 'Teachers'), 'value' => '6'],
                    ['key' => self::pick('Biaya bagi keluarga', 'Cost to families'), 'value' => self::pick('Gratis', 'Free')],
                ],
            ],

            'kambera' => [
                'slug' => 'kambera',
                'href' => self::href('kambera'),
                'level' => 'SMA',
                'age_range' => self::ageRange('SMA'),
                'name' => 'SMA Harapan Kambera',
                'location' => 'Kambera, Sumba Timur',
                'need' => self::pick('Pembaruan laboratorium komputer.', 'Computer laboratory refurbishment.'),
                'status' => self::pick('Perlu 2 mitra korporasi', 'Needs 2 corporate partners'),
                'image' => PlaceholderImage::make(800, 1000, self::pick(
                    'Foto: murid-murid SMA Harapan Kambera sedang belajar',
                    'Photograph: pupils at SMA Harapan Kambera in class'
                )),
                'lede' => self::pick(
                    'Dua puluh dua komputer melayani seratus delapan puluh murid kelas akhir yang membutuhkannya untuk ujian berbasis komputer. Delapan di antaranya sudah tidak menyala, dan yang tersisa berbagi satu jaringan listrik yang sering padam.',
                    'Twenty-two computers serve a hundred and eighty final-year pupils who need them for computer-based exams. Eight no longer switch on, and the rest share one power circuit that regularly cuts out.'
                ),
                'people' => [
                    PlaceholderImage::make(800, 1000, self::pick('Potret Bapak Umbu Ratu Djima di laboratorium komputer', 'Portrait of Bapak Umbu Ratu Djima in the computer lab'))
                        + ['name' => 'Umbu Ratu Djima'],
                    PlaceholderImage::make(800, 1000, self::pick('Potret Ibu Ruth Malo di ruang guru', 'Portrait of Ibu Ruth Malo in the staff room'))
                        + ['name' => 'Ruth Malo'],
                ],
                'context' => [
                    'heading' => self::pick(
                        'Ujian berbasis komputer bukan pilihan lagi.',
                        'Computer-based exams are no longer optional.'
                    ),
                    'body' => self::pick(
                        'Pemerintah mewajibkan ujian akhir berbasis komputer, tetapi laboratorium sekolah dibeli tahun 2014 dan tidak pernah diperbarui. Murid bergiliran dalam sesi ujian yang jauh lebih panjang dari seharusnya.',
                        "The government now requires final exams to run on computers, but the school's lab was bought in 2014 and never upgraded. Pupils sit exams in shifts that run far longer than they should."
                    ),
                    'image' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: baris komputer tua di laboratorium, beberapa mati',
                        'Photograph: a row of ageing computers in the lab, several switched off'
                    )),
                ],
                'work' => [
                    'heading' => self::pick(
                        'Memperbarui, bukan mengganti semuanya sekaligus.',
                        'Refurbishing, not replacing everything at once.'
                    ),
                    'body' => self::pick(
                        'Rencananya bertahap: memperbaiki jaringan listrik terlebih dahulu, lalu mengganti unit yang benar-benar rusak setiap semester sampai laboratorium penuh berfungsi lagi.',
                        'The plan is staged: fix the electrical circuit first, then replace the units that are beyond repair one term at a time until the lab is fully working again.'
                    ),
                    'image' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: teknisi memeriksa kabel jaringan listrik laboratorium',
                        "Photograph: a technician checking the lab's wiring"
                    )),
                ],
                'evidence' => [
                    'before' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: laboratorium komputer dengan unit yang tidak menyala',
                        'Photograph: the computer lab with units that will not switch on'
                    )) + ['caption' => self::pick('April 2026 — delapan unit tidak berfungsi', 'April 2026 — eight units out of service')],
                    'after' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: jaringan listrik laboratorium yang telah diperbaiki',
                        "Photograph: the lab's repaired electrical circuit"
                    )) + ['caption' => self::pick('Agustus 2026 — jaringan listrik selesai diperbaiki', 'August 2026 — the electrical circuit repair complete')],
                ],
                'facts' => [
                    ['key' => self::pick('Dibuka', 'Opened'), 'value' => '2005'],
                    ['key' => self::pick('Murid', 'Pupils'), 'value' => '180'],
                    ['key' => self::pick('Guru', 'Teachers'), 'value' => '14'],
                    ['key' => self::pick('Biaya bagi keluarga', 'Cost to families'), 'value' => self::pick('Gratis', 'Free')],
                ],
            ],

            'melolo' => [
                'slug' => 'melolo',
                'href' => self::href('melolo'),
                'level' => 'TK',
                'age_range' => self::ageRange('TK'),
                'name' => 'TK Harapan Melolo',
                'location' => 'Melolo, Sumba Timur',
                'need' => self::pick('Perlengkapan bermain dan belajar.', 'Play and learning equipment.'),
                'status' => self::pick('Didanai penuh tahun ini', 'Fully funded this year'),
                'image' => PlaceholderImage::make(800, 1000, self::pick(
                    'Foto: murid-murid TK Harapan Melolo sedang bermain',
                    'Photograph: pupils at TK Harapan Melolo at play'
                )),
                'lede' => self::pick(
                    'Delapan puluh anak usia dini bermain dan belajar di satu ruang terbuka yang dibagi dengan tikar dan rak rendah. Tahun lalu perlengkapan bermainnya sudah usang; tahun ini, berkat satu mitra jangka panjang, semuanya baru.',
                    'Eighty young children play and learn in one open room divided by mats and low shelving. Last year the play equipment was worn through; this year, thanks to one long-term partner, all of it is new.'
                ),
                'people' => [
                    PlaceholderImage::make(800, 1000, self::pick('Potret Ibu Dorkas Lende di ruang bermain', 'Portrait of Ibu Dorkas Lende in the play room'))
                        + ['name' => 'Dorkas Lende'],
                    PlaceholderImage::make(800, 1000, self::pick('Potret Ibu Naomi Rambu di halaman TK', 'Portrait of Ibu Naomi Rambu in the kindergarten yard'))
                        + ['name' => 'Naomi Rambu'],
                ],
                'context' => [
                    'heading' => self::pick(
                        'Perlengkapan yang sudah dipakai sejak sekolah dibuka.',
                        'Equipment that has been in use since the school opened.'
                    ),
                    'body' => self::pick(
                        'Balok kayu, puzzle dan alat menggambar sebagian besar berasal dari sumbangan awal tahun 2011 dan sudah retak atau hilang bagiannya. Guru menambal dengan bahan seadanya agar anak-anak tetap punya sesuatu untuk dipegang.',
                        "Wooden blocks, puzzles and drawing materials mostly date from the school's opening donation in 2011 and are cracked or missing pieces. Teachers patch what they can so the children still have something to hold."
                    ),
                    'image' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: rak perlengkapan bermain yang sudah usang',
                        'Photograph: the shelf of worn play equipment'
                    )),
                ],
                'work' => [
                    'heading' => self::pick(
                        'Satu mitra menutup seluruh kebutuhan tahun ini.',
                        'One partner covered the full need this year.'
                    ),
                    'body' => self::pick(
                        'Sebuah yayasan mitra mendanai penggantian penuh perlengkapan bermain dan belajar untuk tahun ajaran ini — balok baru, buku bergambar, dan alat menggambar untuk delapan puluh anak.',
                        "A partner foundation funded a full replacement of the play and learning equipment for this school year — new blocks, picture books and drawing materials for eighty children."
                    ),
                    'image' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: perlengkapan bermain baru tersusun di rak',
                        'Photograph: new play equipment arranged on the shelf'
                    )),
                ],
                'evidence' => [
                    'before' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: perlengkapan bermain lama dan retak',
                        'Photograph: the old, cracked play equipment'
                    )) + ['caption' => self::pick('Februari 2026 — perlengkapan lama masih dipakai', 'February 2026 — the old equipment still in use')],
                    'after' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: anak-anak bermain dengan perlengkapan baru',
                        'Photograph: children playing with the new equipment'
                    )) + ['caption' => self::pick('Agustus 2026 — perlengkapan baru terpasang penuh', 'August 2026 — the new equipment fully in place')],
                ],
                'facts' => [
                    ['key' => self::pick('Dibuka', 'Opened'), 'value' => '2011'],
                    ['key' => self::pick('Murid', 'Pupils'), 'value' => '80'],
                    ['key' => self::pick('Guru', 'Teachers'), 'value' => '4'],
                    ['key' => self::pick('Biaya bagi keluarga', 'Cost to families'), 'value' => self::pick('Gratis', 'Free')],
                ],
            ],

            'lewa' => [
                'slug' => 'lewa',
                'href' => self::href('lewa'),
                'level' => 'SMP',
                'age_range' => self::ageRange('SMP'),
                'name' => 'SMP Harapan Lewa',
                'location' => 'Lewa, Sumba Timur',
                'need' => self::pick('Asrama putri untuk murid dari desa jauh.', "A girls' dormitory for pupils from distant villages."),
                'status' => self::pick('Pembangunan sedang berjalan', 'Construction underway'),
                'image' => PlaceholderImage::make(800, 1000, self::pick(
                    'Foto: murid-murid SMP Harapan Lewa sedang belajar',
                    'Photograph: pupils at SMP Harapan Lewa in class'
                )),
                'lede' => self::pick(
                    'Sepertiga murid perempuan di sekolah ini berjalan lebih dari dua jam setiap hari dari desa-desa di perbukitan. Sebagian berhenti sekolah begitu musim hujan membuat jalan itu tidak bisa dilewati.',
                    "A third of this school's girls walk more than two hours each day from villages in the hills. Some stop attending once the wet season makes that walk impassable."
                ),
                'people' => [
                    PlaceholderImage::make(800, 1000, self::pick('Potret Ibu Melkiana Tamu Ina di depan pondasi asrama', 'Portrait of Ibu Melkiana Tamu Ina at the dormitory foundation'))
                        + ['name' => 'Melkiana Tamu Ina'],
                    PlaceholderImage::make(800, 1000, self::pick('Potret Bapak Yoram Dapa Loka di halaman sekolah', 'Portrait of Bapak Yoram Dapa Loka in the school yard'))
                        + ['name' => 'Yoram Dapa Loka'],
                ],
                'context' => [
                    'heading' => self::pick(
                        'Jarak, bukan minat belajar, yang menghentikan mereka.',
                        'Distance, not the will to learn, is what stops them.'
                    ),
                    'body' => self::pick(
                        'Jalan dari desa-desa terjauh menyeberangi dua sungai musiman. Saat air naik, murid perempuan yang biasanya berjalan bersama rombongan memilih tinggal di rumah daripada menyeberang sendiri.',
                        'The road from the furthest villages crosses two seasonal rivers. When the water rises, girls who normally walk in a group choose to stay home rather than cross alone.'
                    ),
                    'image' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: jalan setapak menuju sekolah melintasi sungai musiman',
                        'Photograph: the footpath to school crossing a seasonal river'
                    )),
                ],
                'work' => [
                    'heading' => self::pick(
                        'Membangun tempat tinggal, bukan hanya jalan pintas.',
                        'Building somewhere to stay, not just a shortcut.'
                    ),
                    'body' => self::pick(
                        'Pondasi asrama putri untuk dua puluh empat tempat tidur sudah selesai. Dinding dan atap sedang dikerjakan bertahap oleh tukang setempat, mengikuti dana yang masuk setiap bulan.',
                        "The foundation for a twenty-four-bed girls' dormitory is complete. Walls and roofing are being built in stages by local builders, following the funds that come in each month."
                    ),
                    'image' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: pondasi asrama putri yang sudah selesai',
                        "Photograph: the completed dormitory foundation"
                    )),
                ],
                'evidence' => [
                    'before' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: lahan kosong sebelum pembangunan asrama',
                        'Photograph: the empty plot before dormitory construction'
                    )) + ['caption' => self::pick('Januari 2026 — lahan sebelum digali', 'January 2026 — the plot before groundwork began')],
                    'after' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: pondasi asrama putri yang sudah selesai',
                        'Photograph: the completed dormitory foundation'
                    )) + ['caption' => self::pick('Agustus 2026 — pondasi selesai, dinding mulai dikerjakan', 'August 2026 — foundation complete, walls underway')],
                ],
                'facts' => [
                    ['key' => self::pick('Dibuka', 'Opened'), 'value' => '2008'],
                    ['key' => self::pick('Murid', 'Pupils'), 'value' => '145'],
                    ['key' => self::pick('Guru', 'Teachers'), 'value' => '9'],
                    ['key' => self::pick('Biaya bagi keluarga', 'Cost to families'), 'value' => self::pick('Gratis', 'Free')],
                ],
            ],

            'waikabubak' => [
                'slug' => 'waikabubak',
                'href' => self::href('waikabubak'),
                'level' => 'SMA',
                'age_range' => self::ageRange('SMA'),
                'name' => 'SMA Harapan Waikabubak',
                'location' => 'Waikabubak, Sumba Barat',
                'need' => self::pick('Beasiswa kelas akhir untuk 12 murid.', 'Final-year scholarships for 12 pupils.'),
                'status' => self::pick('Butuh 5 mitra lagi', 'Needs 5 more partners'),
                'image' => PlaceholderImage::make(800, 1000, self::pick(
                    'Foto: murid-murid SMA Harapan Waikabubak sedang belajar',
                    'Photograph: pupils at SMA Harapan Waikabubak in class'
                )),
                'lede' => self::pick(
                    'Dua belas murid kelas akhir memenuhi syarat masuk universitas tahun ini, tetapi enam di antaranya berisiko putus sebelum ujian akhir karena orang tua mereka tidak lagi mampu membayar biaya transportasi dan buku ujian.',
                    "Twelve final-year pupils qualify for university entry this year, but six of them risk dropping out before finals because their parents can no longer cover transport and exam-book costs."
                ),
                'people' => [
                    PlaceholderImage::make(800, 1000, self::pick('Potret Ibu Rambu Kahi di ruang guru', 'Portrait of Ibu Rambu Kahi in the staff room'))
                        + ['name' => 'Rambu Kahi'],
                    PlaceholderImage::make(800, 1000, self::pick('Potret Bapak Markus Wadu di perpustakaan sekolah', 'Portrait of Bapak Markus Wadu in the school library'))
                        + ['name' => 'Markus Wadu'],
                ],
                'context' => [
                    'heading' => self::pick(
                        'Lulus ujian bukan jaminan bisa mengikutinya.',
                        'Qualifying for the exam is not the same as being able to sit it.'
                    ),
                    'body' => self::pick(
                        'Ujian akhir diadakan di kota kabupaten, dua jam dari rumah sebagian besar murid. Ongkos transportasi dan buku persiapan ujian setara dengan upah sebulan orang tua mereka.',
                        "Finals are held in the district town, two hours from most pupils' homes. Transport and exam-preparation books together cost about a month of their parents' wages."
                    ),
                    'image' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: murid kelas akhir belajar bersama menjelang ujian',
                        'Photograph: final-year pupils studying together ahead of exams'
                    )),
                ],
                'work' => [
                    'heading' => self::pick(
                        'Beasiswa yang menutup biaya, bukan uang saku.',
                        'A scholarship that covers costs, not pocket money.'
                    ),
                    'body' => self::pick(
                        'Setiap beasiswa menanggung ongkos transportasi ke tempat ujian, buku persiapan, dan biaya pendaftaran — dibayarkan langsung ke sekolah, bukan ke murid.',
                        "Each scholarship covers transport to the exam site, preparation books, and the registration fee — paid directly to the school, not to the pupil."
                    ),
                    'image' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: murid menerima buku persiapan ujian dari guru',
                        'Photograph: a pupil receiving exam-preparation books from a teacher'
                    )),
                ],
                'evidence' => [
                    'before' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: daftar murid berisiko putus sebelum ujian akhir',
                        'Photograph: the list of pupils at risk of dropping out before finals'
                    )) + ['caption' => self::pick('Juni 2026 — enam murid berisiko putus sekolah', 'June 2026 — six pupils at risk of dropping out')],
                    'after' => PlaceholderImage::make(1200, 800, self::pick(
                        'Foto: murid kelas akhir belajar bersama menjelang ujian',
                        'Photograph: final-year pupils studying together ahead of exams'
                    )) + ['caption' => self::pick('Agustus 2026 — seluruh dua belas murid terdaftar ujian', 'August 2026 — all twelve pupils registered for finals')],
                ],
                'facts' => [
                    ['key' => self::pick('Dibuka', 'Opened'), 'value' => '2003'],
                    ['key' => self::pick('Murid', 'Pupils'), 'value' => '210'],
                    ['key' => self::pick('Guru', 'Teachers'), 'value' => '16'],
                    ['key' => self::pick('Biaya bagi keluarga', 'Cost to families'), 'value' => self::pick('Gratis', 'Free')],
                ],
            ],
        ];
    }
}
