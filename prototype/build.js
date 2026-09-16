#!/usr/bin/env node
/* ==========================================================================
   Hope for Sumba — prototype generator

   Ten static pages from one shell, so nav, footer and the prototype banner
   can't drift apart. Run: node prototype/build.js

   All content here is INVENTED placeholder material for design review.
   School names, statistics, quotations and the foundation's registration
   details are fictional. Nothing here should be published or quoted.
   ========================================================================== */

const fs = require("fs");
const path = require("path");

const OUT = __dirname;

/* -- helpers ------------------------------------------------------------- */

const esc = (s) =>
  String(s)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");

/** Bilingual attributes. Indonesian is the rendered default. */
const bi = (id, en) => `data-id="${esc(id)}" data-en="${esc(en)}"`;

/** Bilingual inline span. */
const t = (id, en) => `<span ${bi(id, en)}>${esc(id)}</span>`;

/** Photo placeholder. `note` describes the photograph that belongs here. */
const photo = (shape, variant, noteId, noteEn) => `
      <div class="photo photo--${shape}${variant ? ` photo--${variant}` : ""}">
        <p class="photo__note" ${bi(noteId, noteEn)}>${esc(noteId)}</p>
      </div>`;

/* -- shared chrome ------------------------------------------------------- */

const NAV = [
  { href: "index.html", id: "Beranda", en: "Home" },
  { href: "tentang.html", id: "Tentang Kami", en: "About" },
  { href: "sekolah.html", id: "Sekolah", en: "Schools" },
  { href: "rumah-anak.html", id: "Rumah Anak", en: "Children's Homes" },
  { href: "cerita.html", id: "Cerita", en: "Stories" },
  { href: "dukung.html", id: "Dukung Kami", en: "Get Involved" },
  { href: "kontak.html", id: "Kontak", en: "Contact" },
];

function nav(current) {
  const links = NAV.map(
    (item) =>
      `<a href="${item.href}"${
        item.href === current ? ' aria-current="page"' : ""
      } ${bi(item.id, item.en)}>${esc(item.id)}</a>`
  ).join("\n          ");

  return `
  <header class="nav">
    <div class="nav__inner">
      <a class="wordmark" href="index.html">Hope for Sumba</a>
      <nav class="nav__links" aria-label="Utama">
          ${links}
      </nav>
      <div class="langswitch" role="group" aria-label="Bahasa / Language">
        <button type="button" data-lang-btn="id" aria-pressed="true">ID</button>
        <button type="button" data-lang-btn="en" aria-pressed="false">EN</button>
      </div>
    </div>
  </header>`;
}

const PROTOBAR = `
  <div class="protobar">
    <strong ${bi(
      "PROTOTIPE — bukan situs resmi.",
      "PROTOTYPE — not a live site."
    )}>PROTOTIPE — bukan situs resmi.</strong>
    <span ${bi(
      " Semua nama sekolah, angka, foto dan kutipan di halaman ini adalah contoh rekaan untuk keperluan tinjauan desain.",
      " All school names, figures, photographs and quotations on this page are invented examples for design review."
    )}> Semua nama sekolah, angka, foto dan kutipan di halaman ini adalah contoh rekaan untuk keperluan tinjauan desain.</span>
  </div>`;

const THEMESWITCH = `
  <div class="themeswitch" role="group" aria-label="Tema / Theme">
    <p class="label" ${bi("Tampilan", "Appearance")}>Tampilan</p>
    <div class="themeswitch__row">
      <button type="button" data-theme-btn="light" aria-pressed="false" ${bi(
        "Terang",
        "Light"
      )}>Terang</button>
      <button type="button" data-theme-btn="dark" aria-pressed="false" ${bi(
        "Gelap",
        "Dark"
      )}>Gelap</button>
      <button type="button" data-theme-btn="system" aria-pressed="true" ${bi(
        "Sistem",
        "System"
      )}>Sistem</button>
    </div>
  </div>`;

const FOOTER = `
  <footer class="footer">
    <div class="wrap">
      <div class="footer__grid">
        <div class="footer__col">
          <p class="wordmark" style="color:var(--inverse-ink)">Hope for Sumba</p>
          <p ${bi(
            "Sekolah gratis dan rumah anak di Pulau Sumba, Nusa Tenggara Timur.",
            "Free schools and children's homes on Sumba Island, East Nusa Tenggara."
          )}>Sekolah gratis dan rumah anak di Pulau Sumba, Nusa Tenggara Timur.</p>
        </div>
        <div class="footer__col">
          <p class="label" ${bi("Jelajahi", "Explore")}>Jelajahi</p>
          <a href="tentang.html" ${bi("Tentang Kami", "About")}>Tentang Kami</a>
          <a href="sekolah.html" ${bi("Sekolah", "Schools")}>Sekolah</a>
          <a href="rumah-anak.html" ${bi("Rumah Anak", "Children's Homes")}>Rumah Anak</a>
          <a href="cerita.html" ${bi("Cerita", "Stories")}>Cerita</a>
        </div>
        <div class="footer__col">
          <p class="label" ${bi("Hubungi", "Contact")}>Hubungi</p>
          <a href="dukung.html" ${bi("Dukung Kami", "Get Involved")}>Dukung Kami</a>
          <a href="kontak.html" ${bi("Kontak", "Contact")}>Kontak</a>
          <a href="perlindungan-anak.html" ${bi(
            "Perlindungan Anak",
            "Child Safeguarding"
          )}>Perlindungan Anak</a>
        </div>
      </div>
      <div class="footer__legal">
        <p ${bi(
          "Yayasan Harapan Sumba — Jl. Contoh No. 00, Waingapu, Sumba Timur, NTT 87112",
          "Yayasan Harapan Sumba — Jl. Contoh No. 00, Waingapu, Sumba Timur, NTT 87112"
        )}>Yayasan Harapan Sumba — Jl. Contoh No. 00, Waingapu, Sumba Timur, NTT 87112</p>
        <p ${bi(
          "Akta pendirian dan nomor registrasi yayasan: CONTOH — belum diisi. Laporan tahunan tersedia atas permintaan.",
          "Deed of establishment and foundation registration number: PLACEHOLDER — not yet supplied. Annual reports available on request."
        )}>Akta pendirian dan nomor registrasi yayasan: CONTOH — belum diisi. Laporan tahunan tersedia atas permintaan.</p>
      </div>
    </div>
  </footer>`;

/* -- shell --------------------------------------------------------------- */

/* Pages are emitted as fragments, not full documents: the Artifact host wraps
   the entry page in its own <html>/<head>/<body>, and a browser opening one of
   these files directly wraps it just the same. One output shape works for both. */
function shell(page) {
  /* The entry page's title names the whole prototype (it becomes the artifact's
     name); inner pages name themselves. */
  const title =
    page.file === "index.html"
      ? "Hope for Sumba Prototype"
      : `${page.title} — Hope for Sumba (prototype)`;

  return `<title>${esc(title)}</title>
<meta name="robots" content="noindex, nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..700;1,9..144,400..600&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/site.css">
<script>
  /* Runs before the rest of the page parses, so an explicitly chosen theme
     never flashes the other one on navigation. "system" deliberately sets
     nothing and lets prefers-color-scheme decide. */
  try {
    var saved = localStorage.getItem("hfs-theme");
    if (saved === "light" || saved === "dark") {
      document.documentElement.setAttribute("data-theme", saved);
    }
    document.documentElement.setAttribute("lang", localStorage.getItem("hfs-proto-lang") || "id");
  } catch (e) {}
</script>
${PROTOBAR}
${nav(page.file)}
<main>
${page.body}
</main>
${FOOTER}
${THEMESWITCH}
<script src="assets/site.js"></script>
`;
}

/* -- shared content blocks ----------------------------------------------- */

const NEXT_STEP = `
  <section class="section section--inverse">
    <div class="wrap">
      <div class="grid-2" style="align-items:center">
        <div class="stack stack--sm">
          <h2 ${bi("Mari mulai kemitraan.", "Let's start a partnership.")}>Mari mulai kemitraan.</h2>
          <p ${bi(
            "Untuk yayasan, gereja dan perusahaan yang ingin membangun program jangka panjang di Sumba. Kami mengirimkan proposal, anggaran terperinci dan laporan berkala.",
            "For foundations, churches and companies looking to build a long-term programme in Sumba. We provide a proposal, an itemised budget and regular reporting."
          )}>Untuk yayasan, gereja dan perusahaan yang ingin membangun program jangka panjang di Sumba. Kami mengirimkan proposal, anggaran terperinci dan laporan berkala.</p>
        </div>
        <div class="btn-row">
          <a class="btn btn--primary" href="kontak.html" ${bi(
            "Bermitra dengan Kami",
            "Partner with us"
          )}>Bermitra dengan Kami</a>
          <a class="btn btn--secondary" href="dukung.html" ${bi(
            "Dukung Sebuah Sekolah",
            "Support a school"
          )}>Dukung Sebuah Sekolah</a>
        </div>
      </div>
    </div>
  </section>`;

const SCHOOLS = [
  {
    href: "sekolah-karuni.html",
    level: "TK",
    name: "TK Harapan Karuni",
    loc: "Karuni, Sumba Barat Daya",
    needId: "Ruang baca baru untuk 60 anak.",
    needEn: "A new reading room for 60 children.",
    statusId: "Butuh 4 mitra lagi",
    statusEn: "Needs 4 more partners",
    variant: "2",
  },
  {
    href: "sekolah-karuni.html",
    level: "SMP",
    name: "SMP Harapan Anakalang",
    loc: "Anakalang, Sumba Tengah",
    needId: "Guru IPA untuk tahun ajaran baru.",
    needEn: "A science teacher for the new school year.",
    statusId: "Sedang mencari mitra pendidik",
    statusEn: "Seeking a teaching partner",
    variant: "3",
  },
  {
    href: "sekolah-karuni.html",
    level: "SMA",
    name: "SMA Harapan Kambera",
    loc: "Kambera, Sumba Timur",
    needId: "Pembaruan laboratorium komputer.",
    needEn: "Computer laboratory refurbishment.",
    statusId: "Perlu 2 mitra korporasi",
    statusEn: "Needs 2 corporate partners",
    variant: "4",
  },
  {
    href: "sekolah-karuni.html",
    level: "TK",
    name: "TK Harapan Melolo",
    loc: "Melolo, Sumba Timur",
    needId: "Perlengkapan bermain dan belajar.",
    needEn: "Play and learning equipment.",
    statusId: "Didanai penuh tahun ini",
    statusEn: "Fully funded this year",
    variant: "3",
  },
  {
    href: "sekolah-karuni.html",
    level: "SMP",
    name: "SMP Harapan Lewa",
    loc: "Lewa, Sumba Timur",
    needId: "Asrama putri untuk murid dari desa jauh.",
    needEn: "A girls' dormitory for pupils from distant villages.",
    statusId: "Pembangunan sedang berjalan",
    statusEn: "Construction underway",
    variant: "2",
  },
  {
    href: "sekolah-karuni.html",
    level: "SMA",
    name: "SMA Harapan Waikabubak",
    loc: "Waikabubak, Sumba Barat",
    needId: "Beasiswa kelas akhir untuk 12 murid.",
    needEn: "Final-year scholarships for 12 pupils.",
    statusId: "Butuh 5 mitra lagi",
    statusEn: "Needs 5 more partners",
    variant: "4",
  },
];

function schoolCard(s) {
  return `
        <a class="card" href="${s.href}">
          <div class="card__media">
            <span class="badge">${s.level}</span>
            ${photo(
              "tall",
              s.variant,
              `Foto: murid ${s.name} sedang belajar`,
              `Photograph: pupils at ${s.name} in class`
            ).trim()}
          </div>
          <div class="card__body">
            <p class="card__location">${esc(s.loc)}</p>
            <p class="card__title">${esc(s.name)}</p>
            <p class="card__need" ${bi(s.needId, s.needEn)}>${esc(s.needId)}</p>
            <p class="card__status" ${bi(s.statusId, s.statusEn)}>${esc(s.statusId)}</p>
          </div>
        </a>`;
}

const STORIES = [
  {
    href: "cerita-detail.html",
    name: "Rambu",
    variant: "2",
    hookId: "Berjalan sembilan kilometer setiap pagi — sekarang ia mengajar adik kelasnya membaca.",
    hookEn: "She walked nine kilometres each morning — now she teaches the younger pupils to read.",
  },
  {
    href: "cerita-detail.html",
    name: "Ibu Maria Bulu",
    variant: "3",
    hookId: "Kepala sekolah yang kembali ke desanya setelah sebelas tahun merantau.",
    hookEn: "A head teacher who came back to her village after eleven years away.",
  },
  {
    href: "cerita-detail.html",
    name: "Umbu",
    variant: "4",
    hookId: "Ia membongkar radio rusak untuk belajar elektronika. Kini ia di kelas akhir SMA.",
    hookEn: "He took apart broken radios to teach himself electronics. He is now in his final year.",
  },
];

function storyCard(s) {
  return `
        <a class="card" href="${s.href}">
          ${photo(
            "tall",
            s.variant,
            `Foto: potret lingkungan ${s.name}`,
            `Photograph: environmental portrait of ${s.name}`
          ).trim()}
          <div class="card__body">
            <p class="card__title">${esc(s.name)}</p>
            <p class="card__need" ${bi(s.hookId, s.hookEn)}>${esc(s.hookId)}</p>
          </div>
        </a>`;
}

/* -- pages --------------------------------------------------------------- */

const pages = [];

/* Home ------------------------------------------------------------------- */
pages.push({
  file: "index.html",
  title: "Beranda",
  body: `
  <section class="section">
    <div class="wrap">
      <div class="grid-2" style="align-items:center">
        <div class="stack">
          <div class="stack stack--sm">
            <h1 class="display" ${bi(
              "Setiap anak berhak atas masa depan yang cerah.",
              "Every child deserves a bright future."
            )}>Setiap anak berhak atas masa depan yang cerah.</h1>
            <p class="lead muted" ${bi(
              "Sekolah gratis dan rumah anak di Sumba, dibangun bersama warga dan mitra jangka panjang.",
              "Free schools and children's homes in Sumba, built together with local families and long-term partners."
            )}>Sekolah gratis dan rumah anak di Sumba, dibangun bersama warga dan mitra jangka panjang.</p>
          </div>
          <div class="btn-row">
            <a class="btn btn--primary" href="kontak.html" ${bi(
              "Bermitra dengan Kami",
              "Partner with us"
            )}>Bermitra dengan Kami</a>
            <a class="btn btn--secondary" href="dukung.html" ${bi(
              "Dukung Sebuah Sekolah",
              "Support a school"
            )}>Dukung Sebuah Sekolah</a>
          </div>
        </div>
        ${photo(
          "hero",
          "",
          "Foto: anak-anak berjalan di jalan setapak menuju sekolah, cahaya pagi, dataran tinggi Sumba",
          "Photograph: children walking a grass path to school at golden hour, Sumba highlands"
        )}
      </div>
    </div>
  </section>

  <section class="section section--raised">
    <div class="wrap">
      <div class="prose">
        <p class="label muted" ${bi("Siapa kami", "Who we are")}>Siapa kami</p>
        <h2 ${bi(
          "Kami membangun sekolah di tempat yang belum punya sekolah.",
          "We build schools where there were none."
        )}>Kami membangun sekolah di tempat yang belum punya sekolah.</h2>
        <p ${bi(
          "Sumba adalah pulau savana dengan desa-desa yang tersebar jauh. Bagi banyak keluarga, sekolah terdekat berjarak beberapa jam berjalan kaki, dan guru sulit didatangkan. Sejak 2007 kami bekerja bersama tetua desa, gereja setempat dan pemerintah daerah untuk membuka sekolah gratis — dan menjaganya tetap berjalan.",
          "Sumba is a savanna island of widely scattered villages. For many families the nearest school is hours away on foot, and teachers are hard to bring in. Since 2007 we have worked with village elders, local churches and the district government to open free schools — and to keep them running."
        )}>Sumba adalah pulau savana dengan desa-desa yang tersebar jauh. Bagi banyak keluarga, sekolah terdekat berjarak beberapa jam berjalan kaki, dan guru sulit didatangkan. Sejak 2007 kami bekerja bersama tetua desa, gereja setempat dan pemerintah daerah untuk membuka sekolah gratis — dan menjaganya tetap berjalan.</p>
      </div>
    </div>
  </section>

  <section class="section section--inverse">
    <div class="wrap">
      <div class="grid-3">
        <div>
          <p class="stat__value">14</p>
          <p class="stat__label" ${bi(
            "Sekolah dan rumah anak yang aktif",
            "Schools and children's homes running"
          )}>Sekolah dan rumah anak yang aktif</p>
          <p class="stat__asof" ${bi("Per Agustus 2026", "As of August 2026")}>Per Agustus 2026</p>
        </div>
        <div>
          <p class="stat__value">612</p>
          <p class="stat__label" ${bi(
            "Anak bersekolah tahun ini",
            "Children in school this year"
          )}>Anak bersekolah tahun ini</p>
          <p class="stat__asof" ${bi("Per Agustus 2026", "As of August 2026")}>Per Agustus 2026</p>
        </div>
        <div>
          <p class="stat__value">19</p>
          <p class="stat__label" ${bi(
            "Tahun bekerja di Sumba",
            "Years working in Sumba"
          )}>Tahun bekerja di Sumba</p>
          <p class="stat__asof" ${bi("Sejak 2007", "Since 2007")}>Sejak 2007</p>
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="wrap">
      <div class="section-head">
        <p class="label muted" ${bi("Sekolah pilihan", "Featured school")}>Sekolah pilihan</p>
        <h2 ${bi("TK Harapan Karuni", "TK Harapan Karuni")}>TK Harapan Karuni</h2>
      </div>
      <div class="grid-2" style="align-items:center">
        ${photo(
          "hero",
          "2",
          "Foto: ruang kelas TK Karuni saat pelajaran pagi",
          "Photograph: the TK Karuni classroom during morning lessons"
        )}
        <div class="stack">
          <div class="stack stack--sm">
            <span class="badge">TK</span>
            <p class="muted">Karuni, Sumba Barat Daya</p>
            <p ${bi(
              "Enam puluh anak belajar di dua ruang kelas. Ruang ketiga akan menjadi perpustakaan pertama di desa ini — tempat anak-anak bisa membaca setelah jam sekolah, dan tempat orang tua belajar membaca bersama mereka.",
              "Sixty children learn in two classrooms. A third room will become the village's first library — somewhere children can read after school, and where parents learn to read alongside them."
            )}>Enam puluh anak belajar di dua ruang kelas. Ruang ketiga akan menjadi perpustakaan pertama di desa ini — tempat anak-anak bisa membaca setelah jam sekolah, dan tempat orang tua belajar membaca bersama mereka.</p>
            <p class="card__status" ${bi("Butuh 4 mitra lagi", "Needs 4 more partners")}>Butuh 4 mitra lagi</p>
          </div>
          <div class="btn-row">
            <a class="btn btn--primary" href="sekolah-karuni.html" ${bi(
              "Lihat Sekolah Ini",
              "See this school"
            )}>Lihat Sekolah Ini</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section section--raised">
    <div class="wrap">
      <div class="section-head">
        <p class="label muted" ${bi("Cerita", "Stories")}>Cerita</p>
        <h2 ${bi("Orang-orang di balik angka.", "The people behind the numbers.")}>Orang-orang di balik angka.</h2>
      </div>
      <div class="grid-3">
${STORIES.map(storyCard).join("\n")}
      </div>
    </div>
  </section>
${NEXT_STEP}`,
});

/* About ------------------------------------------------------------------ */
pages.push({
  file: "tentang.html",
  title: "Tentang Kami",
  body: `
  <section class="section">
    <div class="wrap">
      <div class="prose">
        <p class="label muted" ${bi("Tentang kami", "About us")}>Tentang kami</p>
        <h1 ${bi(
          "Dimulai dari satu ruang kelas beratap seng.",
          "It started with one classroom under a tin roof."
        )}>Dimulai dari satu ruang kelas beratap seng.</h1>
        <p class="lead" ${bi(
          "Pada 2007, tujuh anak berkumpul di bawah atap seng di Karuni karena sekolah terdekat berjarak tiga jam berjalan kaki. Hari ini empat belas sekolah dan rumah anak berjalan di seluruh pulau.",
          "In 2007, seven children gathered under a tin roof in Karuni because the nearest school was a three-hour walk away. Today fourteen schools and children's homes run across the island."
        )}>Pada 2007, tujuh anak berkumpul di bawah atap seng di Karuni karena sekolah terdekat berjarak tiga jam berjalan kaki. Hari ini empat belas sekolah dan rumah anak berjalan di seluruh pulau.</p>
      </div>
    </div>
  </section>

  <section class="section section--raised">
    <div class="wrap">
      <div class="grid-2" style="align-items:center">
        ${photo(
          "hero",
          "3",
          "Foto: potret lingkungan Reynold di halaman sekolah pertama",
          "Photograph: environmental portrait of Reynold in the yard of the first school"
        )}
        <div class="prose">
          <p class="label muted" ${bi("Pendiri", "Founder")}>Pendiri</p>
          <h2 ${bi("Reynold", "Reynold")}>Reynold</h2>
          <p ${bi(
            "Reynold lahir dan besar di Sumba. Ia kembali setelah menyelesaikan studinya, membawa satu keyakinan sederhana: anak-anak di kampungnya sama cerdasnya dengan anak-anak di mana pun, dan yang mereka butuhkan hanyalah pintu yang terbuka.",
            "Reynold was born and raised in Sumba. He came back after finishing his studies with one simple conviction: the children in his village are as capable as children anywhere, and what they need is an open door."
          )}>Reynold lahir dan besar di Sumba. Ia kembali setelah menyelesaikan studinya, membawa satu keyakinan sederhana: anak-anak di kampungnya sama cerdasnya dengan anak-anak di mana pun, dan yang mereka butuhkan hanyalah pintu yang terbuka.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="wrap">
      <div class="grid-2">
        <div class="prose">
          <p class="label muted" ${bi("Misi", "Mission")}>Misi</p>
          <h2 ${bi(
            "Pendidikan gratis yang bermutu, dekat dengan rumah.",
            "Free, good education, close to home."
          )}>Pendidikan gratis yang bermutu, dekat dengan rumah.</h2>
          <p ${bi(
            "Kami membuka dan menjalankan sekolah di desa-desa yang belum terlayani, dengan guru yang tinggal di desa itu sendiri, dan tanpa biaya apa pun bagi keluarga.",
            "We open and run schools in underserved villages, staffed by teachers who live in those villages, at no cost to families."
          )}>Kami membuka dan menjalankan sekolah di desa-desa yang belum terlayani, dengan guru yang tinggal di desa itu sendiri, dan tanpa biaya apa pun bagi keluarga.</p>
        </div>
        <div class="prose">
          <p class="label muted" ${bi("Visi", "Vision")}>Visi</p>
          <h2 ${bi(
            "Sumba yang menyekolahkan anaknya sendiri.",
            "A Sumba that educates its own children."
          )}>Sumba yang menyekolahkan anaknya sendiri.</h2>
          <p ${bi(
            "Setiap sekolah dirancang untuk suatu hari dijalankan sepenuhnya oleh guru dan pemimpin dari Sumba. Beberapa lulusan pertama kami kini mengajar di kelas tempat mereka dulu belajar.",
            "Every school is designed to one day be run entirely by teachers and leaders from Sumba. Several of our first graduates now teach in the classrooms where they studied."
          )}>Setiap sekolah dirancang untuk suatu hari dijalankan sepenuhnya oleh guru dan pemimpin dari Sumba. Beberapa lulusan pertama kami kini mengajar di kelas tempat mereka dulu belajar.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="section section--sunk">
    <div class="wrap">
      <div class="prose" style="margin-inline:auto;text-align:center">
        <p class="quote" ${bi(
          "“Saya ingin murid-murid saya tahu bahwa dari desa kecil ini, mereka bisa menjadi apa saja.”",
          "“I want my pupils to know that from this small village, they can become anything.”"
        )}>“Saya ingin murid-murid saya tahu bahwa dari desa kecil ini, mereka bisa menjadi apa saja.”</p>
        <p class="attribution">Maria Bulu — <span ${bi(
          "Kepala Sekolah, SMP Harapan Anakalang",
          "Head Teacher, SMP Harapan Anakalang"
        )}>Kepala Sekolah, SMP Harapan Anakalang</span></p>
      </div>
    </div>
  </section>
${NEXT_STEP}`,
});

/* Schools directory ------------------------------------------------------ */
pages.push({
  file: "sekolah.html",
  title: "Sekolah",
  body: `
  <section class="section">
    <div class="wrap">
      <div class="prose">
        <p class="label muted" ${bi("Sekolah", "Schools")}>Sekolah</p>
        <h1 ${bi("Empat belas sekolah, satu pulau.", "Fourteen schools, one island.")}>Empat belas sekolah, satu pulau.</h1>
        <p class="lead" ${bi(
          "Setiap sekolah dibuka atas permintaan desanya sendiri, dan setiap sekolah punya kebutuhan yang berbeda. Berikut enam di antaranya.",
          "Each school was opened at the request of its own village, and each has different needs. Six of them are shown here."
        )}>Setiap sekolah dibuka atas permintaan desanya sendiri, dan setiap sekolah punya kebutuhan yang berbeda. Berikut enam di antaranya.</p>
      </div>
    </div>
  </section>

  <section class="section section--raised">
    <div class="wrap">
      <div class="grid-3">
${SCHOOLS.map(schoolCard).join("\n")}
      </div>
    </div>
  </section>
${NEXT_STEP}`,
});

/* School detail ---------------------------------------------------------- */
pages.push({
  file: "sekolah-karuni.html",
  title: "TK Harapan Karuni",
  body: `
  <section class="section section--tight">
    <div class="wrap">
      <div class="stack stack--sm" style="margin-bottom:var(--s-6)">
        <span class="badge" style="align-self:flex-start">TK</span>
        <h1>TK Harapan Karuni</h1>
        <p class="lead muted">Karuni, Sumba Barat Daya</p>
      </div>
      ${photo(
        "wide",
        "2",
        "Foto: halaman TK Karuni saat jam istirahat, sudut pandang setinggi mata anak",
        "Photograph: the TK Karuni yard at break time, shot at the children's eye level"
      )}
    </div>
  </section>

  <section class="section section--raised">
    <div class="wrap">
      <div class="prose">
        <p class="label muted" ${bi("Siapa mereka", "Who they are")}>Siapa mereka</p>
        <h2 ${bi("Enam puluh anak, dua ruang kelas.", "Sixty children, two classrooms.")}>Enam puluh anak, dua ruang kelas.</h2>
        <p ${bi(
          "TK Harapan Karuni dibuka pada 2009 dan merupakan sekolah kedua yang kami dirikan. Tiga guru mengajar di sini, dua di antaranya tumbuh besar di desa ini. Anak-anak datang berjalan kaki dari empat kampung di sekitarnya.",
          "TK Harapan Karuni opened in 2009 and was the second school we established. Three teachers work here, two of whom grew up in this village. The children walk in from four surrounding hamlets."
        )}>TK Harapan Karuni dibuka pada 2009 dan merupakan sekolah kedua yang kami dirikan. Tiga guru mengajar di sini, dua di antaranya tumbuh besar di desa ini. Anak-anak datang berjalan kaki dari empat kampung di sekitarnya.</p>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="wrap">
      <div class="grid-2" style="align-items:center">
        <div class="prose">
          <p class="label muted" ${bi("Tantangannya", "The challenge")}>Tantangannya</p>
          <h2 ${bi(
            "Tidak ada tempat membaca setelah pulang sekolah.",
            "There is nowhere to read after school."
          )}>Tidak ada tempat membaca setelah pulang sekolah.</h2>
          <p ${bi(
            "Di Karuni belum ada perpustakaan, dan sebagian besar rumah belum punya aliran listrik untuk membaca setelah gelap. Buku yang ada disimpan di lemari ruang guru dan hanya bisa dipakai saat jam pelajaran.",
            "Karuni has no library, and most homes have no electricity to read by after dark. The books the school owns are kept in a cupboard in the staff room and can only be used during lessons."
          )}>Di Karuni belum ada perpustakaan, dan sebagian besar rumah belum punya aliran listrik untuk membaca setelah gelap. Buku yang ada disimpan di lemari ruang guru dan hanya bisa dipakai saat jam pelajaran.</p>
        </div>
        ${photo(
          "hero",
          "3",
          "Foto: lemari buku di ruang guru, sore hari",
          "Photograph: the book cupboard in the staff room, late afternoon"
        )}
      </div>
    </div>
  </section>

  <section class="section section--sunk">
    <div class="wrap">
      <div class="section-head">
        <p class="label muted" ${bi("Yang sedang berjalan", "What is underway")}>Yang sedang berjalan</p>
        <h2 ${bi("Ruang ketiga menjadi perpustakaan.", "The third room becomes a library.")}>Ruang ketiga menjadi perpustakaan.</h2>
      </div>
      <div class="beforeafter">
        <div>
          ${photo(
            "story",
            "4",
            "Foto: ruang ketiga sebelum dikerjakan",
            "Photograph: the third room before work began"
          )}
          <p class="beforeafter__caption" ${bi(
            "Maret 2026 — ruang ketiga, belum terpakai",
            "March 2026 — the third room, unused"
          )}>Maret 2026 — ruang ketiga, belum terpakai</p>
        </div>
        <div>
          ${photo(
            "story",
            "2",
            "Foto: rangka atap dan rak pertama terpasang",
            "Photograph: roof frame and the first shelving installed"
          )}
          <p class="beforeafter__caption" ${bi(
            "Agustus 2026 — atap dan rak pertama terpasang",
            "August 2026 — roof and first shelving in place"
          )}>Agustus 2026 — atap dan rak pertama terpasang</p>
        </div>
      </div>
    </div>
  </section>

  <section class="section section--raised">
    <div class="wrap">
      <div class="grid-2">
        <div class="prose">
          <p class="label muted" ${bi("Kebutuhan saat ini", "Current need")}>Kebutuhan saat ini</p>
          <h2 ${bi("Rak, buku, dan penerangan.", "Shelving, books, and light.")}>Rak, buku, dan penerangan.</h2>
          <p ${bi(
            "Pekerjaan bangunan hampir selesai. Yang belum tersedia adalah rak untuk sisi kedua ruangan, koleksi buku berbahasa Indonesia untuk usia dini, dan satu panel surya kecil agar ruangan bisa dipakai sampai malam.",
            "The building work is nearly done. What is still missing is shelving for the second side of the room, a collection of early-years books in Indonesian, and one small solar panel so the room can be used into the evening."
          )}>Pekerjaan bangunan hampir selesai. Yang belum tersedia adalah rak untuk sisi kedua ruangan, koleksi buku berbahasa Indonesia untuk usia dini, dan satu panel surya kecil agar ruangan bisa dipakai sampai malam.</p>
          <p class="card__status" ${bi("Butuh 4 mitra lagi", "Needs 4 more partners")}>Butuh 4 mitra lagi</p>
        </div>
        <dl class="facts">
          <div class="facts__row">
            <dt class="facts__key" ${bi("Dibuka", "Opened")}>Dibuka</dt>
            <dd class="facts__value">2009</dd>
          </div>
          <div class="facts__row">
            <dt class="facts__key" ${bi("Jenjang", "Level")}>Jenjang</dt>
            <dd class="facts__value">TK</dd>
          </div>
          <div class="facts__row">
            <dt class="facts__key" ${bi("Murid", "Pupils")}>Murid</dt>
            <dd class="facts__value">60</dd>
          </div>
          <div class="facts__row">
            <dt class="facts__key" ${bi("Guru", "Teachers")}>Guru</dt>
            <dd class="facts__value">3</dd>
          </div>
          <div class="facts__row">
            <dt class="facts__key" ${bi("Biaya bagi keluarga", "Cost to families")}>Biaya bagi keluarga</dt>
            <dd class="facts__value" ${bi("Gratis", "Free")}>Gratis</dd>
          </div>
        </dl>
      </div>
    </div>
  </section>
${NEXT_STEP}`,
});

/* Children's homes ------------------------------------------------------- */
pages.push({
  file: "rumah-anak.html",
  title: "Rumah Anak",
  body: `
  <section class="section">
    <div class="wrap">
      <div class="prose">
        <p class="label muted" ${bi("Rumah anak", "Children's homes")}>Rumah anak</p>
        <h1 ${bi("Rumah, bukan asrama.", "A home, not an institution.")}>Rumah, bukan asrama.</h1>
        <p class="lead" ${bi(
          "Dua rumah anak kami menampung anak-anak yang tidak bisa tinggal bersama keluarganya. Setiap rumah diasuh oleh satu keluarga pengasuh yang tinggal bersama anak-anak sepanjang tahun.",
          "Our two children's homes care for children who cannot live with their families. Each home is run by a resident house family who live alongside the children year-round."
        )}>Dua rumah anak kami menampung anak-anak yang tidak bisa tinggal bersama keluarganya. Setiap rumah diasuh oleh satu keluarga pengasuh yang tinggal bersama anak-anak sepanjang tahun.</p>
      </div>
    </div>
  </section>

  <section class="section section--raised">
    <div class="wrap">
      <div class="grid-2" style="align-items:center">
        ${photo(
          "hero",
          "2",
          "Foto: dapur rumah anak saat makan malam bersama — tanpa wajah anak yang dapat dikenali",
          "Photograph: the house kitchen at the shared evening meal — no identifiable children's faces"
        )}
        <div class="prose">
          <p class="label muted" ${bi("Cara kami mengasuh", "Our care model")}>Cara kami mengasuh</p>
          <h2 ${bi("Satu keluarga, bukan giliran jaga.", "One family, not a staff rota.")}>Satu keluarga, bukan giliran jaga.</h2>
          <p ${bi(
            "Anak-anak tinggal dalam kelompok kecil bersama satu keluarga pengasuh tetap, bersekolah di sekolah umum bersama anak-anak desa lainnya, dan tetap berhubungan dengan kerabatnya jika memungkinkan. Tujuan kami selalu pengasuhan yang stabil dan jangka panjang.",
            "Children live in small groups with one permanent house family, attend the local school alongside other village children, and stay in contact with relatives wherever that is possible. The goal is always stable, long-term care."
          )}>Anak-anak tinggal dalam kelompok kecil bersama satu keluarga pengasuh tetap, bersekolah di sekolah umum bersama anak-anak desa lainnya, dan tetap berhubungan dengan kerabatnya jika memungkinkan. Tujuan kami selalu pengasuhan yang stabil dan jangka panjang.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="wrap">
      <div class="section-head">
        <p class="label muted" ${bi("Privasi anak", "Children's privacy")}>Privasi anak</p>
        <h2 ${bi(
          "Anak-anak di rumah anak tidak kami tampilkan dengan nama.",
          "Children in our homes are never named on this site."
        )}>Anak-anak di rumah anak tidak kami tampilkan dengan nama.</h2>
      </div>
      <div class="prose">
        <p ${bi(
          "Keadaan keluarga seorang anak adalah informasi pribadi miliknya, bukan milik kami, dan bukan bahan penggalangan dana. Kami menceritakan pekerjaan rumah anak ini melalui pengasuh, melalui kegiatan sehari-hari, dan melalui anak-anak yang sudah dewasa dan memilih sendiri untuk bercerita.",
          "A child's family circumstances are their own private information — not ours, and not fundraising material. We tell the story of this work through the house parents, through daily life, and through young adults who have grown up and chosen to speak for themselves."
        )}>Keadaan keluarga seorang anak adalah informasi pribadi miliknya, bukan milik kami, dan bukan bahan penggalangan dana. Kami menceritakan pekerjaan rumah anak ini melalui pengasuh, melalui kegiatan sehari-hari, dan melalui anak-anak yang sudah dewasa dan memilih sendiri untuk bercerita.</p>
        <p><a href="perlindungan-anak.html" ${bi(
          "Baca kebijakan perlindungan anak kami →",
          "Read our child safeguarding policy →"
        )}>Baca kebijakan perlindungan anak kami →</a></p>
      </div>
    </div>
  </section>
${NEXT_STEP}`,
});

/* Stories listing -------------------------------------------------------- */
pages.push({
  file: "cerita.html",
  title: "Cerita",
  body: `
  <section class="section">
    <div class="wrap">
      <div class="prose">
        <p class="label muted" ${bi("Cerita", "Stories")}>Cerita</p>
        <h1 ${bi("Orang-orang di balik angka.", "The people behind the numbers.")}>Orang-orang di balik angka.</h1>
        <p class="lead" ${bi(
          "Murid, guru, dan warga desa yang membangun sekolah-sekolah ini — diceritakan dengan seizin mereka.",
          "Pupils, teachers and the villagers who built these schools — told with their permission."
        )}>Murid, guru, dan warga desa yang membangun sekolah-sekolah ini — diceritakan dengan seizin mereka.</p>
      </div>
    </div>
  </section>

  <section class="section section--raised">
    <div class="wrap">
      <div class="grid-3">
${STORIES.map(storyCard).join("\n")}
${storyCard({
  href: "cerita-detail.html",
  name: "Tamu",
  variant: "3",
  hookId: "Lulusan pertama dari Lewa yang kembali sebagai guru matematika.",
  hookEn: "The first Lewa graduate to come back as a mathematics teacher.",
})}
${storyCard({
  href: "cerita-detail.html",
  name: "Bapak Yohanis Rangga",
  variant: "4",
  hookId: "Tukang kayu desa yang membangun enam ruang kelas pertama kami.",
  hookEn: "The village carpenter who built our first six classrooms.",
})}
${storyCard({
  href: "cerita-detail.html",
  name: "Ledu",
  variant: "2",
  hookId: "Tahun lalu ia belum bisa membaca. Sekarang ia membacakan cerita untuk adiknya.",
  hookEn: "A year ago he could not read. Now he reads aloud to his younger sister.",
})}
      </div>
    </div>
  </section>
${NEXT_STEP}`,
});

/* Story detail ----------------------------------------------------------- */
pages.push({
  file: "cerita-detail.html",
  title: "Cerita Rambu",
  body: `
  <section class="section section--tight">
    <div class="wrap">
      <div class="prose" style="margin-bottom:var(--s-6)">
        <p class="label muted" ${bi("Cerita murid", "Pupil story")}>Cerita murid</p>
        <h1 ${bi(
          "Sembilan kilometer, setiap pagi.",
          "Nine kilometres, every morning."
        )}>Sembilan kilometer, setiap pagi.</h1>
        <p class="lead muted" ${bi(
          "Rambu, kelas akhir SMA Harapan Kambera",
          "Rambu, final year, SMA Harapan Kambera"
        )}>Rambu, kelas akhir SMA Harapan Kambera</p>
      </div>
      ${photo(
        "wide",
        "3",
        "Foto: potret lingkungan Rambu di depan kelas, setinggi mata",
        "Photograph: environmental portrait of Rambu outside her classroom, at eye level"
      )}
    </div>
  </section>

  <section class="section section--raised">
    <div class="wrap">
      <div class="prose">
        <p ${bi(
          "Rambu berangkat pukul lima pagi. Jalan dari kampungnya menurun melewati padang, menyeberangi satu sungai kecil yang meluap di musim hujan, lalu naik lagi ke jalan beraspal tempat sekolahnya berada. Sembilan kilometer. Ia melakukannya selama tiga tahun.",
          "Rambu leaves at five in the morning. The path from her hamlet drops down across the grassland, crosses a small river that floods in the wet season, then climbs back up to the paved road where her school stands. Nine kilometres. She has done it for three years."
        )}>Rambu berangkat pukul lima pagi. Jalan dari kampungnya menurun melewati padang, menyeberangi satu sungai kecil yang meluap di musim hujan, lalu naik lagi ke jalan beraspal tempat sekolahnya berada. Sembilan kilometer. Ia melakukannya selama tiga tahun.</p>
        <p ${bi(
          "Tahun ini ia mengajar membaca untuk murid kelas satu dua kali seminggu, sebelum jam pelajarannya sendiri dimulai. Gurunya bilang ia menjelaskan lebih sabar daripada sebagian orang dewasa.",
          "This year she teaches reading to the first-year pupils twice a week, before her own lessons begin. Her teacher says she explains things more patiently than some adults do."
        )}>Tahun ini ia mengajar membaca untuk murid kelas satu dua kali seminggu, sebelum jam pelajarannya sendiri dimulai. Gurunya bilang ia menjelaskan lebih sabar daripada sebagian orang dewasa.</p>
      </div>
    </div>
  </section>

  <section class="section section--sunk">
    <div class="wrap">
      <div class="prose" style="margin-inline:auto;text-align:center">
        <p class="quote" ${bi(
          "“Saya ingin jadi guru. Bukan di kota — di sini.”",
          "“I want to be a teacher. Not in the city — here.”"
        )}>“Saya ingin jadi guru. Bukan di kota — di sini.”</p>
        <p class="attribution">Rambu</p>
      </div>
    </div>
  </section>
${NEXT_STEP}`,
});

/* Get involved ----------------------------------------------------------- */
const TIERS = [
  {
    id: "Ruang kelas",
    en: "A classroom",
    costId: "Rp 180.000.000",
    costEn: "Rp 180,000,000 (approx. USD 11,000)",
    descId: "Satu ruang kelas lengkap dengan meja, kursi dan papan tulis.",
    descEn: "One complete classroom with desks, chairs and a board.",
    variant: "2",
  },
  {
    id: "Laboratorium",
    en: "A laboratory",
    costId: "Rp 240.000.000",
    costEn: "Rp 240,000,000 (approx. USD 14,500)",
    descId: "Laboratorium IPA atau komputer untuk satu sekolah menengah.",
    descEn: "A science or computer laboratory for one secondary school.",
    variant: "3",
  },
  {
    id: "Gaji guru satu tahun",
    en: "A teacher for a year",
    costId: "Rp 54.000.000",
    costEn: "Rp 54,000,000 (approx. USD 3,300)",
    descId: "Satu guru tetap, tinggal di desa tempat ia mengajar.",
    descEn: "One permanent teacher, living in the village where they teach.",
    variant: "4",
  },
  {
    id: "Perpustakaan",
    en: "A library",
    costId: "Rp 95.000.000",
    costEn: "Rp 95,000,000 (approx. USD 5,800)",
    descId: "Rak, koleksi buku dan penerangan tenaga surya.",
    descEn: "Shelving, a book collection and solar lighting.",
    variant: "2",
  },
  {
    id: "Dua puluh laptop",
    en: "Twenty laptops",
    costId: "Rp 120.000.000",
    costEn: "Rp 120,000,000 (approx. USD 7,300)",
    descId: "Perangkat untuk satu kelas komputer, termasuk perawatan.",
    descEn: "Devices for one computer class, maintenance included.",
    variant: "3",
  },
  {
    id: "Beasiswa satu murid",
    en: "One pupil's scholarship",
    costId: "Rp 7.200.000",
    costEn: "Rp 7,200,000 (approx. USD 440)",
    descId: "Satu tahun penuh: seragam, buku, makan siang dan transportasi.",
    descEn: "A full year: uniform, books, lunch and transport.",
    variant: "4",
  },
];

pages.push({
  file: "dukung.html",
  title: "Dukung Kami",
  body: `
  <section class="section">
    <div class="wrap">
      <div class="prose">
        <p class="label muted" ${bi("Dukung kami", "Get involved")}>Dukung kami</p>
        <h1 ${bi("Pilih sesuatu yang nyata.", "Fund something specific.")}>Pilih sesuatu yang nyata.</h1>
        <p class="lead" ${bi(
          "Setiap bentuk dukungan di bawah ini terhubung dengan satu sekolah dan satu kebutuhan yang bisa Anda lihat perkembangannya. Kami mengirimkan laporan foto dan keuangan untuk setiap program.",
          "Every option below is tied to one school and one need whose progress you can follow. We send photographic and financial reporting on each."
        )}>Setiap bentuk dukungan di bawah ini terhubung dengan satu sekolah dan satu kebutuhan yang bisa Anda lihat perkembangannya. Kami mengirimkan laporan foto dan keuangan untuk setiap program.</p>
      </div>
    </div>
  </section>

  <section class="section section--raised">
    <div class="wrap">
      <div class="grid-3">
${TIERS.map(
  (tier) => `
        <div class="card">
          ${photo(
            "story",
            tier.variant,
            `Foto: ${tier.id.toLowerCase()} di salah satu sekolah`,
            `Photograph: ${tier.en.toLowerCase()} at one of the schools`
          ).trim()}
          <div class="card__body">
            <p class="card__title" ${bi(tier.id, tier.en)}>${esc(tier.id)}</p>
            <p class="card__status" ${bi(tier.costId, tier.costEn)}>${esc(tier.costId)}</p>
            <p class="card__need" ${bi(tier.descId, tier.descEn)}>${esc(tier.descId)}</p>
          </div>
        </div>`
).join("\n")}
      </div>
    </div>
  </section>

  <section class="section">
    <div class="wrap">
      <div class="grid-2">
        <div class="prose">
          <p class="label muted" ${bi("Cara memberi", "How giving works")}>Cara memberi</p>
          <h2 ${bi(
            "Transfer langsung ke rekening yayasan.",
            "A direct transfer to the foundation."
          )}>Transfer langsung ke rekening yayasan.</h2>
          <p ${bi(
            "Kami belum menggunakan gerbang pembayaran daring. Pemberian dilakukan melalui transfer bank, QRIS, Wise atau PayPal, langsung ke rekening atas nama Yayasan Harapan Sumba.",
            "We do not yet use an online payment gateway. Giving is by bank transfer, QRIS, Wise or PayPal, straight into an account held in the name of Yayasan Harapan Sumba."
          )}>Kami belum menggunakan gerbang pembayaran daring. Pemberian dilakukan melalui transfer bank, QRIS, Wise atau PayPal, langsung ke rekening atas nama Yayasan Harapan Sumba.</p>
          <p ${bi(
            "Setelah transfer, kirimkan bukti kepada kami dan Anda akan menerima tanda terima resmi dalam tiga hari kerja, beserta nama sekolah yang menerima dukungan Anda.",
            "After transferring, send us the receipt and you will have a formal acknowledgement within three working days, along with the name of the school your gift went to."
          )}>Setelah transfer, kirimkan bukti kepada kami dan Anda akan menerima tanda terima resmi dalam tiga hari kerja, beserta nama sekolah yang menerima dukungan Anda.</p>
        </div>
        <dl class="facts">
          <div class="facts__row">
            <dt class="facts__key" ${bi("Nama rekening", "Account name")}>Nama rekening</dt>
            <dd class="facts__value">Yayasan Harapan Sumba</dd>
          </div>
          <div class="facts__row">
            <dt class="facts__key" ${bi("Bank", "Bank")}>Bank</dt>
            <dd class="facts__value" ${bi("CONTOH — belum diisi", "PLACEHOLDER — not yet supplied")}>CONTOH — belum diisi</dd>
          </div>
          <div class="facts__row">
            <dt class="facts__key" ${bi("Nomor rekening", "Account number")}>Nomor rekening</dt>
            <dd class="facts__value" ${bi("CONTOH — belum diisi", "PLACEHOLDER — not yet supplied")}>CONTOH — belum diisi</dd>
          </div>
          <div class="facts__row">
            <dt class="facts__key" ${bi("Internasional", "International")}>Internasional</dt>
            <dd class="facts__value">Wise / PayPal</dd>
          </div>
          <div class="facts__row">
            <dt class="facts__key" ${bi("Tanda terima", "Acknowledgement")}>Tanda terima</dt>
            <dd class="facts__value" ${bi("3 hari kerja", "3 working days")}>3 hari kerja</dd>
          </div>
        </dl>
      </div>
    </div>
  </section>

  <section class="section section--sunk">
    <div class="wrap">
      <div class="grid-3">
        <div class="prose">
          <h3 ${bi("Perusahaan", "Companies")}>Perusahaan</h3>
          <p ${bi(
            "Program CSR jangka panjang dengan anggaran terperinci, kunjungan lapangan dan laporan tahunan yang dapat diaudit.",
            "Long-term CSR programmes with itemised budgets, site visits and auditable annual reporting."
          )}>Program CSR jangka panjang dengan anggaran terperinci, kunjungan lapangan dan laporan tahunan yang dapat diaudit.</p>
        </div>
        <div class="prose">
          <h3 ${bi("Gereja", "Churches")}>Gereja</h3>
          <p ${bi(
            "Kemitraan jemaat dengan satu sekolah tertentu, termasuk kabar berkala dan kunjungan bersama.",
            "Congregational partnership with one named school, including regular updates and joint visits."
          )}>Kemitraan jemaat dengan satu sekolah tertentu, termasuk kabar berkala dan kunjungan bersama.</p>
        </div>
        <div class="prose">
          <h3 ${bi("Relawan", "Volunteers")}>Relawan</h3>
          <p ${bi(
            "Kami menerima relawan untuk pelatihan guru dan pembangunan. Semua relawan menjalani proses penyaringan perlindungan anak.",
            "We host volunteers for teacher training and construction. All volunteers complete a child safeguarding screening process."
          )}>Kami menerima relawan untuk pelatihan guru dan pembangunan. Semua relawan menjalani proses penyaringan perlindungan anak.</p>
        </div>
      </div>
    </div>
  </section>
${NEXT_STEP}`,
});

/* Contact ---------------------------------------------------------------- */
pages.push({
  file: "kontak.html",
  title: "Kontak",
  body: `
  <section class="section">
    <div class="wrap">
      <div class="prose">
        <p class="label muted" ${bi("Kontak", "Contact")}>Kontak</p>
        <h1 ${bi("Mari bicara.", "Let's talk.")}>Mari bicara.</h1>
        <p class="lead" ${bi(
          "Untuk pertanyaan kemitraan, permintaan proposal, atau kunjungan ke Sumba. Kami biasanya membalas dalam dua hari kerja.",
          "For partnership enquiries, proposal requests, or visits to Sumba. We normally reply within two working days."
        )}>Untuk pertanyaan kemitraan, permintaan proposal, atau kunjungan ke Sumba. Kami biasanya membalas dalam dua hari kerja.</p>
      </div>
    </div>
  </section>

  <section class="section section--raised">
    <div class="wrap">
      <div class="grid-2">
        <div class="stack">
          <h2 ${bi("Pertanyaan kemitraan", "Partnership enquiry")}>Pertanyaan kemitraan</h2>
          <form class="stack stack--sm" onsubmit="event.preventDefault()">
            <label class="stack stack--sm" for="c-name">
              <span class="label muted" ${bi("Nama", "Name")}>Nama</span>
              <input id="c-name" name="name" type="text" style="font:inherit;padding:12px;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--surface);color:var(--ink)">
            </label>
            <label class="stack stack--sm" for="c-org">
              <span class="label muted" ${bi("Organisasi", "Organisation")}>Organisasi</span>
              <input id="c-org" name="org" type="text" style="font:inherit;padding:12px;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--surface);color:var(--ink)">
            </label>
            <label class="stack stack--sm" for="c-email">
              <span class="label muted" ${bi("Surel", "Email")}>Surel</span>
              <input id="c-email" name="email" type="email" style="font:inherit;padding:12px;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--surface);color:var(--ink)">
            </label>
            <label class="stack stack--sm" for="c-msg">
              <span class="label muted" ${bi("Pesan", "Message")}>Pesan</span>
              <textarea id="c-msg" name="message" rows="5" style="font:inherit;padding:12px;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--surface);color:var(--ink)"></textarea>
            </label>
            <div class="btn-row">
              <button class="btn btn--primary" type="submit" ${bi(
                "Kirim Pesan",
                "Send message"
              )}>Kirim Pesan</button>
            </div>
            <p class="caption muted" ${bi(
              "Formulir ini tidak aktif pada prototipe.",
              "This form is inactive in the prototype."
            )}>Formulir ini tidak aktif pada prototipe.</p>
          </form>
        </div>
        <div class="stack">
          <h2 ${bi("Temui kami", "Find us")}>Temui kami</h2>
          <dl class="facts">
            <div class="facts__row">
              <dt class="facts__key" ${bi("Surel", "Email")}>Surel</dt>
              <dd class="facts__value">halo@contoh.org</dd>
            </div>
            <div class="facts__row">
              <dt class="facts__key" ${bi("Telepon", "Phone")}>Telepon</dt>
              <dd class="facts__value" ${bi("CONTOH — belum diisi", "PLACEHOLDER — not yet supplied")}>CONTOH — belum diisi</dd>
            </div>
            <div class="facts__row">
              <dt class="facts__key" ${bi("Kantor", "Office")}>Kantor</dt>
              <dd class="facts__value">Waingapu, Sumba Timur</dd>
            </div>
          </dl>
          ${photo(
            "story",
            "3",
            "Peta: lokasi kantor di Waingapu (Google Maps pada situs sebenarnya)",
            "Map: office location in Waingapu (Google Maps on the live site)"
          )}
        </div>
      </div>
    </div>
  </section>`,
});

/* Safeguarding ----------------------------------------------------------- */
pages.push({
  file: "perlindungan-anak.html",
  title: "Perlindungan Anak",
  body: `
  <section class="section">
    <div class="wrap">
      <div class="prose">
        <p class="label muted" ${bi("Perlindungan anak", "Child safeguarding")}>Perlindungan anak</p>
        <h1 ${bi(
          "Bagaimana kami melindungi anak-anak di situs ini.",
          "How we protect the children on this site."
        )}>Bagaimana kami melindungi anak-anak di situs ini.</h1>
        <p class="lead" ${bi(
          "Kami bekerja dengan anak-anak. Karena itu, cara kami menampilkan mereka di internet diatur oleh aturan tertulis, bukan oleh kebiasaan.",
          "We work with children. How we show them on the internet is therefore governed by written rules, not by habit."
        )}>Kami bekerja dengan anak-anak. Karena itu, cara kami menampilkan mereka di internet diatur oleh aturan tertulis, bukan oleh kebiasaan.</p>
      </div>
    </div>
  </section>

  <section class="section section--raised">
    <div class="wrap">
      <div class="prose">
        <h2 ${bi("Aturan kami", "Our rules")}>Aturan kami</h2>
        <p ${bi(
          "Anak-anak hanya disebut dengan nama depan. Kami tidak pernah mencantumkan nama keluarga seorang anak, dan tidak pernah menggabungkan nama seorang anak dengan lokasi tertentu beserta kebiasaan hariannya.",
          "Children are referred to by first name only. We never publish a child's family name, and we never combine a child's name with a specific location and a daily routine."
        )}>Anak-anak hanya disebut dengan nama depan. Kami tidak pernah mencantumkan nama keluarga seorang anak, dan tidak pernah menggabungkan nama seorang anak dengan lokasi tertentu beserta kebiasaan hariannya.</p>
        <p ${bi(
          "Setiap foto anak memerlukan izin tertulis dari orang tua atau wali, serta persetujuan anak itu sendiri, khusus untuk penggunaan di situs web. Izin untuk buletin cetak bukan izin untuk internet.",
          "Every photograph of a child requires written consent from a parent or guardian, and the child's own assent, specifically for use on the website. Consent for a printed newsletter is not consent for the internet."
        )}>Setiap foto anak memerlukan izin tertulis dari orang tua atau wali, serta persetujuan anak itu sendiri, khusus untuk penggunaan di situs web. Izin untuk buletin cetak bukan izin untuk internet.</p>
        <p ${bi(
          "Data lokasi pada setiap foto dihapus otomatis sebelum diunggah. Anak-anak yang tinggal di rumah anak tidak pernah ditampilkan dengan nama, dan kami tidak pernah menjelaskan alasan seorang anak berada dalam pengasuhan kami.",
          "Location data is stripped from every photograph before upload. Children living in our homes are never named, and we never explain why a child is in our care."
        )}>Data lokasi pada setiap foto dihapus otomatis sebelum diunggah. Anak-anak yang tinggal di rumah anak tidak pernah ditampilkan dengan nama, dan kami tidak pernah menjelaskan alasan seorang anak berada dalam pengasuhan kami.</p>
        <p ${bi(
          "Izin dapat dicabut kapan saja. Jika sebuah keluarga meminta, foto dan cerita anak mereka kami turunkan dari situs ini pada hari yang sama.",
          "Consent can be withdrawn at any time. If a family asks, their child's photographs and story come off this site the same day."
        )}>Izin dapat dicabut kapan saja. Jika sebuah keluarga meminta, foto dan cerita anak mereka kami turunkan dari situs ini pada hari yang sama.</p>
      </div>
    </div>
  </section>

  <section class="section section--sunk">
    <div class="wrap">
      <div class="prose">
        <h2 ${bi("Meminta penghapusan", "Requesting removal")}>Meminta penghapusan</h2>
        <p ${bi(
          "Jika Anda adalah orang tua, wali, atau anak yang pernah kami tampilkan dan ingin gambar atau cerita itu dihapus, hubungi kami. Anda tidak perlu memberikan alasan.",
          "If you are a parent, guardian, or a young person we have featured, and you want an image or story removed, contact us. You do not need to give a reason."
        )}>Jika Anda adalah orang tua, wali, atau anak yang pernah kami tampilkan dan ingin gambar atau cerita itu dihapus, hubungi kami. Anda tidak perlu memberikan alasan.</p>
        <p><a href="kontak.html" ${bi(
          "Hubungi kami →",
          "Contact us →"
        )}>Hubungi kami →</a></p>
      </div>
    </div>
  </section>`,
});

/* -- write --------------------------------------------------------------- */

for (const page of pages) {
  fs.writeFileSync(path.join(OUT, page.file), shell(page));
  console.log("wrote", page.file);
}
console.log(`\n${pages.length} pages built.`);
