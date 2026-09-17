<?php

namespace App\ViewModels;

use App\ViewModels\Concerns\ResolvesLocale;

/**
 * FIXTURE — awaiting replacement by App\Models\SponsorshipTier (Agent B's
 * lane, see docs/data-contract.md § SponsorshipTier). Content reused from
 * prototype/build.js's TIERS array.
 *
 * Costs are stored/displayed in IDR in both locales; `costApprox` is
 * populated only for /en, from a single hardcoded illustrative rate
 * (spec decision 9) — no exchange-rate API, no scheduled job.
 */
class TierData
{
    use ResolvesLocale;

    /** @return array<int, array> */
    public static function all(): array
    {
        return [
            [
                'title' => self::pick('Ruang kelas', 'A classroom'),
                'cost' => 'Rp 180.000.000',
                'costApprox' => self::approx('11,000'),
                'description' => self::pick(
                    'Satu ruang kelas lengkap dengan meja, kursi dan papan tulis.',
                    'One complete classroom with desks, chairs and a board.'
                ),
                'image' => PlaceholderImage::make(1200, 800, self::pick('Foto: ruang kelas di salah satu sekolah', 'Photograph: a classroom at one of the schools')),
            ],
            [
                'title' => self::pick('Laboratorium', 'A laboratory'),
                'cost' => 'Rp 240.000.000',
                'costApprox' => self::approx('14,500'),
                'description' => self::pick(
                    'Laboratorium IPA atau komputer untuk satu sekolah menengah.',
                    'A science or computer laboratory for one secondary school.'
                ),
                'image' => PlaceholderImage::make(1200, 800, self::pick('Foto: laboratorium di salah satu sekolah', 'Photograph: a laboratory at one of the schools')),
            ],
            [
                'title' => self::pick('Gaji guru satu tahun', 'A teacher for a year'),
                'cost' => 'Rp 54.000.000',
                'costApprox' => self::approx('3,300'),
                'description' => self::pick(
                    'Satu guru tetap, tinggal di desa tempat ia mengajar.',
                    'One permanent teacher, living in the village where they teach.'
                ),
                'image' => PlaceholderImage::make(1200, 800, self::pick('Foto: seorang guru mengajar di kelas', 'Photograph: a teacher at work in class')),
            ],
            [
                'title' => self::pick('Perpustakaan', 'A library'),
                'cost' => 'Rp 95.000.000',
                'costApprox' => self::approx('5,800'),
                'description' => self::pick(
                    'Rak, koleksi buku dan penerangan tenaga surya.',
                    'Shelving, a book collection and solar lighting.'
                ),
                'image' => PlaceholderImage::make(1200, 800, self::pick('Foto: rak buku perpustakaan sekolah', 'Photograph: library shelving at a school')),
            ],
            [
                'title' => self::pick('Dua puluh laptop', 'Twenty laptops'),
                'cost' => 'Rp 120.000.000',
                'costApprox' => self::approx('7,300'),
                'description' => self::pick(
                    'Perangkat untuk satu kelas komputer, termasuk perawatan.',
                    'Devices for one computer class, maintenance included.'
                ),
                'image' => PlaceholderImage::make(1200, 800, self::pick('Foto: kelas komputer di salah satu sekolah', 'Photograph: a computer class at one of the schools')),
            ],
            [
                'title' => self::pick('Beasiswa satu murid', "One pupil's scholarship"),
                'cost' => 'Rp 7.200.000',
                'costApprox' => self::approx('440'),
                'description' => self::pick(
                    'Satu tahun penuh: seragam, buku, makan siang dan transportasi.',
                    'A full year: uniform, books, lunch and transport.'
                ),
                'image' => PlaceholderImage::make(1200, 800, self::pick('Foto: murid dengan seragam dan perlengkapan sekolah', 'Photograph: a pupil with uniform and school supplies')),
            ],
        ];
    }

    private static function approx(string $usd): ?string
    {
        return app()->getLocale() === 'en' ? "approx. USD {$usd}" : null;
    }
}
