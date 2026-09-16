{{-- resources/views/gallery.blade.php --}}
{{-- Development-only page: every section from Tasks 11-15 in one place so the
     team can review the design system without paging through real content.
     Placeholder images come from https://placehold.co - fine for a page that
     never ships to production, but it does mean this page renders broken
     images on a machine with no internet access. --}}
@php
  $image = fn (int $w, int $h, string $alt) => [
      'sources' => ['jpeg' => ["https://placehold.co/{$w}x{$h}/jpeg {$w}w"]],
      'width' => $w,
      'height' => $h,
      'alt' => $alt,
  ];

  $school1 = [
      'href' => '#', 'level' => 'TK', 'name' => 'TK Harapan Karuni',
      'location' => 'Karuni, Sumba Barat Daya',
      'need' => 'Ruang baca baru untuk 60 anak.',
      'status' => 'Butuh 4 mitra lagi',
      'image' => $image(800, 1000, 'Murid-murid di dalam kelas TK Harapan Karuni'),
  ];

  $school2 = [
      'href' => '#', 'level' => 'SD', 'name' => 'SD Kristen Anakalang',
      'location' => 'Anakalang, Sumba Tengah',
      'need' => 'Atap ruang kelas 3 bocor saat musim hujan.',
      'status' => 'Didanai penuh tahun ini',
      'image' => $image(800, 1000, 'Halaman SD Kristen Anakalang'),
  ];

  $school3 = [
      'href' => '#', 'level' => 'Rumah Anak', 'name' => 'Rumah Anak Kambera',
      'location' => 'Kambera, Waingapu',
      'need' => 'Penerangan tenaga surya untuk belajar malam.',
      'status' => 'Butuh 2 mitra lagi',
      'image' => $image(800, 1000, 'Anak-anak belajar di Rumah Anak Kambera'),
  ];

  // Safeguarding: children in story cards are named by first name only,
  // never a surname, never paired with village plus a daily-routine detail
  // specific enough to identify them. See cards/story.blade.php.
  $story1 = [
      'href' => '#', 'name' => 'Rambu',
      'hook' => 'Berjalan sembilan kilometer setiap pagi untuk sampai ke sekolah.',
      'image' => $image(800, 1000, 'Potret lingkungan seorang murid'),
  ];

  $story2 = [
      'href' => '#', 'name' => 'Umbu',
      'hook' => 'Anak pertama di keluarganya yang belajar membaca.',
      'image' => $image(800, 1000, 'Potret lingkungan seorang murid'),
  ];

  $story3 = [
      'href' => '#', 'name' => 'Wangi',
      'hook' => 'Ingin menjadi guru dan mengajar di kampung halamannya sendiri.',
      'image' => $image(800, 1000, 'Potret lingkungan seorang murid'),
  ];
@endphp

<x-layouts.site title="Galeri komponen">
  <div data-section="hero">
    <x-sections.hero
      heading="Setiap anak berhak atas masa depan yang cerah."
      subhead="Sekolah gratis dan rumah anak di Sumba."
      :image="$image(1600, 900, 'Anak-anak berjalan menuju sekolah di Waingapu')" />
  </div>

  <div data-section="lede">
    <x-sections.lede label="Siapa kami" heading="Kami membangun sekolah di tempat yang belum punya sekolah.">
      <p>Sumba adalah pulau savana dengan desa-desa yang tersebar jauh dari kota Waingapu.
         Sejak 2007 kami bekerja bersama komunitas di Karuni, Anakalang dan Kambera.</p>
    </x-sections.lede>
  </div>

  <div data-section="people">
    <x-sections.people label="Orang-orang" heading="Guru dan pengasuh yang menjalankannya setiap hari."
      :portraits="[
        $image(800, 1000, 'Potret guru') + ['name' => 'Maria Bulu'],
        $image(800, 1000, 'Potret guru') + ['name' => 'Yuliana Ndapa'],
        $image(800, 1000, 'Potret pengasuh') + ['name' => 'Yosef Praing'],
      ]" />
  </div>

  <div data-section="context">
    <x-sections.context label="Tantangannya" heading="Tidak ada tempat membaca setelah pulang sekolah."
      :image="$image(1200, 800, 'Lemari buku kosong di ruang guru Karuni')">
      <p>Di Karuni belum ada perpustakaan. Anak-anak yang ingin membaca harus menunggu
         gilirannya memakai buku pelajaran yang jumlahnya terbatas.</p>
    </x-sections.context>
  </div>

  <div data-section="work">
    <x-sections.work label="Yang sedang berjalan" heading="Ruang ketiga sedang diubah menjadi perpustakaan."
      :image="$image(1200, 800, 'Rangka atap terpasang di ruang ketiga')">
      <p>Pekerjaan bangunan hampir selesai. Rak buku dan penerangan menjadi kebutuhan berikutnya.</p>
    </x-sections.work>
  </div>

  <div data-section="stat-band">
    <x-sections.stat-band :stats="[
      ['value' => '14', 'label' => 'Sekolah dan rumah anak aktif', 'asOf' => 'Per Agustus 2026'],
      ['value' => '612', 'label' => 'Anak bersekolah tahun ini', 'asOf' => 'Per Agustus 2026'],
      ['value' => '19', 'label' => 'Tahun bekerja di Sumba', 'asOf' => 'Sejak 2007'],
    ]" />
  </div>

  <div data-section="evidence">
    <x-sections.evidence
      label="Bukti kemajuan" heading="Ruang yang sama, delapan bulan kemudian."
      :before="$image(1200, 800, 'Ruang sebelum dikerjakan') + ['caption' => 'Maret 2026 - ruang ketiga, belum terpakai']"
      :after="$image(1200, 800, 'Rak pertama terpasang') + ['caption' => 'Agustus 2026 - atap dan rak terpasang']" />
  </div>

  <div data-section="quote">
    <x-sections.quote attribution="Maria Bulu" role="Kepala Sekolah, TK Harapan Karuni">
      Saya ingin murid-murid saya tahu bahwa dari desa kecil ini, mereka bisa menjadi apa saja.
    </x-sections.quote>
  </div>

  <div data-section="stories">
    <x-sections.stories label="Cerita" heading="Orang-orang di balik angka."
      :stories="[$story1, $story2, $story3]" />
  </div>

  <div data-section="directory">
    <x-sections.directory label="Sekolah" heading="Empat belas sekolah, satu pulau."
      :schools="[$school1, $school2, $school3]" />
  </div>

  <div data-section="current-need">
    <x-sections.current-need label="Kebutuhan saat ini" heading="Rak, buku, dan penerangan untuk TK Harapan Karuni."
      status="Butuh 4 mitra lagi"
      :facts="[
        ['key' => 'Dibuka', 'value' => '2009'],
        ['key' => 'Murid', 'value' => '60'],
        ['key' => 'Biaya bagi keluarga', 'value' => 'Gratis'],
      ]">
      <p>Pekerjaan bangunan ruang ketiga hampir selesai. Rak buku, koleksi awal, dan
         penerangan tenaga surya adalah yang masih dibutuhkan sebelum ruang ini dibuka.</p>
    </x-sections.current-need>
  </div>

  <div data-section="next-step">
    <x-sections.next-step
      heading="Mari mulai kemitraan."
      body="Untuk yayasan, gereja dan perusahaan yang ingin membangun program jangka panjang di Sumba."
      partnerHref="#partner" giveHref="#give" />
  </div>

  <div data-section="partners">
    <x-sections.partners heading="Mitra kami"
      :partners="[
        ['logo' => 'https://placehold.co/160x60', 'name' => 'Yayasan Pelita Sumba'],
        ['logo' => 'https://placehold.co/160x60', 'name' => 'Gereja Kristen Sumba'],
      ]" />
  </div>
</x-layouts.site>
