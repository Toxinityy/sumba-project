<?php

return [
    'supported' => ['id', 'en'],

    'default' => 'id',

    /*
     | Translated path segments. Keyed by an internal name that never appears
     | in a URL, so route names stay stable while the public path differs per
     | locale. Indonesian visitors get Indonesian URLs, which reads as native
     | and indexes better than an English skeleton with Indonesian content.
     */
    'segments' => [
        'about' => ['id' => 'tentang',           'en' => 'about'],
        'schools' => ['id' => 'sekolah',           'en' => 'schools'],
        'homes' => ['id' => 'rumah-anak',        'en' => 'childrens-homes'],
        'stories' => ['id' => 'cerita',            'en' => 'stories'],
        'give' => ['id' => 'dukung',            'en' => 'get-involved'],
        'contact' => ['id' => 'kontak',            'en' => 'contact'],
        'safeguarding' => ['id' => 'perlindungan-anak', 'en' => 'safeguarding'],
        'gallery' => ['id' => 'galeri',            'en' => 'gallery'],
        'partners' => ['id' => 'mitra',             'en' => 'partners'],
        'impact' => ['id' => 'dampak',            'en' => 'impact'],
        'projects' => ['id' => 'proyek',            'en' => 'projects'],
    ],
];
