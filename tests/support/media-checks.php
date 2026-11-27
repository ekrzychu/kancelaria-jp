<?php

declare(strict_types=1);

use App\Media\ImageProcessor;
use App\Media\MediaPurpose;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

require __DIR__.'/bootstrap-test.php';
$operation = $argv[2];

$folder = storage_path('media-fixtures');
if (! is_dir($folder)) {
    mkdir($folder);
}
$file = fn (string $name): string => $folder.'/'.$name;
$jpeg = imagecreatetruecolor(120, 60);
imagefill($jpeg, 0, 0, imagecolorallocate($jpeg, 110, 32, 58));
imagejpeg($jpeg, $file('orientation.jpg'), 90);
$tiff = "II\x2a\0".pack('V', 8).pack('v', 1).pack('vvVvvV', 274, 3, 1, 6, 0, 0);
$exif = "Exif\0\0".$tiff;
$raw = file_get_contents($file('orientation.jpg'));
file_put_contents($file('orientation.jpg'), substr($raw, 0, 2)."\xff\xe1".pack('n', strlen($exif) + 2).$exif.substr($raw, 2));

$portrait = imagecreatetruecolor(1600, 500);
imagealphablending($portrait, false);
imagesavealpha($portrait, true);
imagefill($portrait, 0, 0, imagecolorallocatealpha($portrait, 39, 10, 26, 127));
imagefilledrectangle($portrait, 100, 70, 1300, 480, imagecolorallocate($portrait, 113, 55, 74));
imagepng($portrait, $file('portrait.png'));

$figure = imagecreatetruecolor(2000, 600);
imagefill($figure, 0, 0, imagecolorallocate($figure, 181, 134, 103));
imagefilledrectangle($figure, 100, 100, 900, 500, imagecolorallocate($figure, 66, 31, 47));
imagewebp($figure, $file('figure.webp'));

$png = file_get_contents($file('portrait.png'));
$webp = file_get_contents($file('figure.webp')).'ANIM'.pack('V', 6).str_repeat("\0", 6);
file_put_contents($file('animated.webp'), substr_replace($webp, pack('V', strlen($webp) - 8), 4, 4));
$chunk = static fn (string $tag, string $data): string => pack('N', strlen($data)).$tag.$data.hash('crc32b', $tag.$data, true);
file_put_contents($file('animated.png'), substr($png, 0, 33).$chunk('acTL', pack('NN', 2, 0)).substr($png, 33));
file_put_contents($file('dimensions.png'), substr($png, 0, 8).$chunk('IHDR', pack('NN', 6000, 5000).substr($png, 24, 5)).substr($png, 33));
file_put_contents($file('truncated.png'), substr($png, 0, 50));
file_put_contents($file('executable.png'), "MZ\0Test bytes");
file_put_contents($file('vector.svg'), '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"/>');
$oversized = fopen($file('oversized.jpg'), 'wb');
ftruncate($oversized, config('media.max_kilobytes') * 1024 + 1);
fclose($oversized);

if ($operation === 'fixtures') {
    echo json_encode(array_combine(
        ['jpeg', 'png', 'webp', 'invalid'],
        array_map($file, ['orientation.jpg', 'portrait.png', 'figure.webp', 'executable.png']),
    ), JSON_THROW_ON_ERROR);
    exit(0);
}

$process = fn (string $name, MediaPurpose $purpose): array => app(ImageProcessor::class)
    ->process(new UploadedFile($file($name), 'untrusted-original-name.svg', 'image/svg+xml', UPLOAD_ERR_OK, true), $purpose);
$ensure = static function (bool $ok, string $case): void {
    if (! $ok) {
        throw new RuntimeException('Media check failed: '.$case);
    }
};

foreach ([
    ['orientation.jpg', MediaPurpose::Article, 60, 120],
    ['portrait.png', MediaPurpose::Portrait, 1200, 375],
    ['figure.webp', MediaPurpose::Article, 1920, 576],
] as [$name, $purpose, $width, $height]) {
    $result = $process($name, $purpose);
    $ensure($result['width'] === $width && $result['height'] === $height, $name.' dimensions');
    $ensure(getimagesizefromstring($result['bytes'])[2] === IMAGETYPE_WEBP, $name.' format');
    $ensure(! str_contains($result['bytes'], 'EXIF') && ! str_contains($result['bytes'], 'GPS'), $name.' metadata');
    if ($name === 'portrait.png') {
        $image = imagecreatefromstring($result['bytes']);
        $ensure((imagecolorat($image, 0, 0) >> 24 & 127) === 127, 'transparent pixel');
    }
}
foreach (['animated.png', 'animated.webp', 'dimensions.png', 'truncated.png', 'executable.png', 'vector.svg', 'oversized.jpg'] as $name) {
    try {
        $process($name, MediaPurpose::Article);
        $ensure(false, $name.' accepted');
    } catch (ValidationException $exception) {
        $ensure(isset($exception->errors()['image']), $name.' validation error');
    }
}

echo 'GD processing, orientation, bounds, transparency, metadata and format rejection passed.'.PHP_EOL;
