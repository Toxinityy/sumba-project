<?php

namespace App\Filament\Resources\Schools\Schemas;

use App\Models\Enums\SchoolLevel;
use App\Models\School;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

/*
 | Translated fields are a JSON column keyed by locale (HasTranslations), so
 | each one is edited as `field.id` and `field.en` — never as a single box,
 | which would write a bare string where the pages expect a locale map.
 |
 | Indonesian is the source language (spec §7): its tab is first, and a page
 | with no English falls back to Indonesian with a note rather than 404ing.
 | So English may be left empty; Indonesian may not.
 */
class SchoolForm
{
    private const NO_FUNDING_AMOUNT = 'not_regex:/(rp|idr|\$|usd)\s*[\d.,]/i';

    /** Translated fields, with their Indonesian label and field type. */
    private const TRANSLATED = [
        'name' => ['Nama sekolah', 'text'],
        'slug' => ['Alamat URL', 'slug'],
        'location' => ['Lokasi', 'text'],
        'status' => ['Status', 'status'],
        'current_need' => ['Kebutuhan saat ini', 'line'],
        'lede' => ['Paragraf pembuka', 'prose'],
        'context_heading' => ['Judul: latar belakang', 'text'],
        'context_body' => ['Isi: latar belakang', 'prose'],
        'work_heading' => ['Judul: pekerjaan kami', 'text'],
        'work_body' => ['Isi: pekerjaan kami', 'prose'],
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()->columnSpanFull()->tabs([
                Tab::make('Bahasa Indonesia')->schema(self::fieldsFor('id')),
                Tab::make('English')->schema(self::fieldsFor('en')),
            ]),

            Section::make('Fakta dan penerbitan')
                ->description('Tidak diterjemahkan: angka dan tanggal sama di kedua bahasa.')
                ->columns(2)
                ->schema([
                    Select::make('level')
                        ->label('Jenjang')
                        ->options(SchoolLevel::class)
                        ->required(),
                    TextInput::make('opened_year')->label('Tahun dibuka')->numeric()->minValue(1)->rules(['integer']),
                    TextInput::make('pupils')->label('Jumlah murid')->numeric()->minValue(0)->rules(['integer']),
                    TextInput::make('teachers')->label('Jumlah guru')->numeric()->minValue(0)->rules(['integer']),
                    DateTimePicker::make('published_at')
                        ->label('Tanggal terbit')
                        ->helperText('Kosongkan untuk menyimpan sebagai draf. Draf tidak tampil di situs.')
                        ->seconds(false),
                ]),
        ]);
    }

    /** @return array<int, mixed> */
    private static function fieldsFor(string $locale): array
    {
        $required = $locale === 'id';

        return array_map(function (array $spec, string $field) use ($locale, $required) {
            [$label, $type] = $spec;
            $name = "{$field}.{$locale}";

            return match ($type) {
                'slug' => TextInput::make($name)
                    ->label($label)
                    ->required($required)
                    ->unique(table: School::class, column: "slug->{$locale}", ignoreRecord: true)
                    // Per-locale slugs (spec §7): /id/sekolah/karuni and
                    // /en/schools/karuni-hope are the same school.
                    ->helperText('Huruf kecil dan tanda hubung, misalnya: harapan-karuni.')
                    ->rules(['regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'])
                    ->maxLength(120),

                // Rule 1, enforced where it is typed rather than caught in
                // review: status is a qualitative sentence, never a funding
                // figure. What is banned is money and percentages — "75%",
                // "Rp 40.000.000", "40 persen", or a bare number. A number
                // inside a sentence is fine and common: "Ruang baca baru
                // untuk 60 anak." is the seeded copy for Karuni.
                'status' => Textarea::make($name)
                    ->label($label)
                    ->required($required)
                    ->rows(2)
                    ->helperText('Satu kalimat. Jangan tulis jumlah dana atau persentase — situs ini tidak menampilkannya.')
                    ->rules([
                        'not_regex:/\d\s*(%|persen|percent)/i',          // 75%, 40 persen
                        self::NO_FUNDING_AMOUNT,                         // Rp 40.000.000
                        'not_regex:/^[\s\d.,%]+$/',                        // nothing but a figure
                    ]),

                'line' => Textarea::make($name)->label($label)->required($required)->rows(2)->rules([self::NO_FUNDING_AMOUNT]),
                'prose' => Textarea::make($name)->label($label)->required($required)->rows(6)->rules([self::NO_FUNDING_AMOUNT]),
                default => TextInput::make($name)->label($label)->required($required)->maxLength(160)->rules([self::NO_FUNDING_AMOUNT]),
            };
        }, self::TRANSLATED, array_keys(self::TRANSLATED));
    }
}
