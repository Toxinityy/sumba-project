<?php

// tests/Feature/Models/ExifStrippingTest.php

use App\Models\MediaAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/*
 | A real JPEG with a GPS block pointing at Sumba, built byte by byte: a TIFF
 | header, IFD0 holding only the GPSInfo pointer, and a GPS IFD with a
 | latitude. Built rather than committed so the fixture is readable here and
 | cannot drift from what the test claims it contains.
 */
function geotaggedJpeg(): string
{
    $image = imagecreatetruecolor(40, 30);
    imagefill($image, 0, 0, imagecolorallocate($image, 200, 120, 60));
    ob_start();
    imagejpeg($image, null, 90);
    $jpeg = ob_get_clean();

    $le16 = fn (int $n) => pack('v', $n);
    $le32 = fn (int $n) => pack('V', $n);
    $entry = fn (int $tag, int $type, int $count, string $value) => $le16($tag).$le16($type).$le32($count).$value;

    $tiff = 'II'.$le16(42).$le32(8)
        .$le16(1).$entry(0x8825, 4, 1, $le32(26)).$le32(0)
        .$le16(2)
        .$entry(0x0001, 2, 2, "S\0\0\0")
        .$entry(0x0002, 5, 3, $le32(56))
        .$le32(0)
        .$le32(9).$le32(1).$le32(40).$le32(1).$le32(0).$le32(1);

    $app1 = "Exif\0\0".$tiff;

    return substr($jpeg, 0, 2)."\xFF\xE1".pack('n', strlen($app1) + 2).$app1.substr($jpeg, 2);
}

function gpsKeys(string $path): array
{
    $exif = @exif_read_data($path) ?: [];

    return array_values(array_filter(array_keys($exif), fn ($key) => str_starts_with($key, 'GPS')));
}

beforeEach(function () {
    Storage::fake('public');
    Storage::disk('public')->put('uploads/geotagged.jpg', geotaggedJpeg());
    $this->file = Storage::disk('public')->path('uploads/geotagged.jpg');

    // Without this the test could pass on a fixture that never had GPS.
    expect(gpsKeys($this->file))->toContain('GPSLatitude');
});

it('strips GPS from a file when its asset is stored', function () {
    MediaAsset::factory()->create(['path' => 'uploads/geotagged.jpg']);

    expect(gpsKeys($this->file))->toBe([])
        ->and(getimagesize($this->file)[0])->toBe(40);
});

it('strips GPS even when the save is refused', function () {
    // A minor with no consent record: the §9 guard refuses the write. The
    // file is already on disk, so it must be clean whether or not it is kept.
    expect(fn () => MediaAsset::factory()->create([
        'path' => 'uploads/geotagged.jpg',
        'depicts_minor' => true,
        'consent_id' => null,
    ]))->toThrow(DomainException::class);

    expect(gpsKeys($this->file))->toBe([]);
});
