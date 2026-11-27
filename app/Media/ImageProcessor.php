<?php

namespace App\Media;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final class ImageProcessor
{
    public function process(UploadedFile $file, MediaPurpose $purpose): array
    {
        if (! $file->isValid() || $file->getSize() < 1 || $file->getSize() > config('media.max_kilobytes') * 1024) {
            $this->reject('Wybierz zdjęcie o rozmiarze do '.(config('media.max_kilobytes') / 1024).' MB.');
        }

        $input = $file->get();
        if (! $input) {
            $this->reject('Nie udało się odczytać pliku.');
        }
        $info = @getimagesizefromstring($input);
        if (! $info || ! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            $this->reject('Wybierz poprawny obraz JPEG, PNG lub WebP.');
        }
        [$width, $height, $type] = $info;
        if ($width < 1 || $height < 1 || max($width, $height) > config('media.max_edge')
            || $width * $height > config('media.max_pixels')) {
            $this->reject('Obraz ma za duże wymiary.');
        }

        $exif = $this->metadata($input, $type);
        if (! extension_loaded('gd') || ! (gd_info()['WebP Support'] ?? false)) {
            throw new RuntimeException('GD with WebP support is required for media uploads.');
        }
        $ignore = ini_get('gd.jpeg_ignore_warning');
        ini_set('gd.jpeg_ignore_warning', '0');
        $decodeFailed = false;
        set_error_handler(static function () use (&$decodeFailed): bool {
            $decodeFailed = true;

            return true;
        }, E_WARNING);
        try {
            $image = imagecreatefromstring($input);
        } finally {
            restore_error_handler();
            if ($ignore !== false) {
                ini_set('gd.jpeg_ignore_warning', $ignore);
            }
        }
        if (! $image || $decodeFailed) {
            $this->reject('Nie udało się odczytać obrazu. Wybierz poprawny plik.');
        }

        if (! imageistruecolor($image) && ! imagepalettetotruecolor($image)) {
            $this->reject('Nie udało się przetworzyć kolorów obrazu.');
        }
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $image = $this->orient($image, $this->orientation($exif));
        $width = imagesx($image);
        $height = imagesy($image);
        $limit = config('media.widths.'.$purpose->value);
        if ($width > $limit) {
            $scaled = @imagescale($image, $limit, max(1, (int) round($height * $limit / $width)));
            if (! $scaled) {
                $this->reject('Nie udało się zmniejszyć obrazu.');
            }
            $image = $scaled;
            imagesavealpha($image, true);
        }

        ob_start();
        try {
            $ok = @imagewebp($image, null, config('media.quality'));
            $bytes = ob_get_contents();
        } finally {
            ob_end_clean();
        }
        $encoded = $bytes ? @getimagesizefromstring($bytes) : false;
        if (! $ok || ! $encoded || $encoded[2] !== IMAGETYPE_WEBP) {
            $this->reject('Nie udało się zapisać obrazu WebP.');
        }

        return ['bytes' => $bytes, 'width' => $encoded[0], 'height' => $encoded[1]];
    }

    private function metadata(string $bytes, int $type): string
    {
        $length = strlen($bytes);
        $exif = '';
        if ($type === IMAGETYPE_JPEG) {
            for ($offset = 2; $offset + 4 <= $length && $bytes[$offset] === "\xff";) {
                $marker = ord($bytes[$offset + 1]);
                if ($marker === 255) {
                    $offset++;
                    continue;
                }
                if ($marker === 218 || $marker === 217) {
                    break;
                }
                $size = unpack('n', $bytes, $offset + 2)[1];
                if ($size < 2 || $offset + 2 + $size > $length) {
                    $this->reject('Plik obrazu jest uszkodzony.');
                }
                if ($marker === 225 && substr($bytes, $offset + 4, 6) === "Exif\0\0") {
                    return substr($bytes, $offset + 10, max(0, $size - 8));
                }
                $offset += 2 + $size;
            }

            return '';
        }

        $png = $type === IMAGETYPE_PNG;
        $offset = $png ? 8 : 12;
        $overhead = $png ? 12 : 8;
        while ($offset + $overhead <= $length) {
            $kind = substr($bytes, $offset + ($png ? 4 : 0), 4);
            $size = unpack($png ? 'N' : 'V', $bytes, $offset + ($png ? 0 : 4))[1];
            $next = $offset + $overhead + $size + ($png ? 0 : $size % 2);
            if ($next > $length) {
                $this->reject('Plik obrazu jest uszkodzony.');
            }
            if (in_array($kind, ['acTL', 'ANIM', 'ANMF'], true)
                || ($kind === 'VP8X' && $size > 0 && (ord($bytes[$offset + 8]) & 2))) {
                $this->reject('Animowane obrazy są niedozwolone.');
            }
            if (in_array($kind, ['eXIf', 'EXIF'], true) && $size <= 65536) {
                $exif = substr($bytes, $offset + 8, $size);
            }
            $offset = $next;
        }

        return str_starts_with($exif, "Exif\0\0") ? substr($exif, 6) : $exif;
    }

    private function orientation(string $exif): int
    {
        $endian = substr($exif, 0, 4);
        if (strlen($exif) < 8 || ! in_array($endian, ["II\x2a\0", "MM\0\x2a"], true)) {
            return 1;
        }

        [$short, $long] = str_starts_with($endian, 'II') ? ['v', 'V'] : ['n', 'N'];
        $ifd = unpack($long, $exif, 4)[1];
        if ($ifd < 8 || $ifd + 2 > strlen($exif)) {
            return 1;
        }
        $count = unpack($short, $exif, $ifd)[1];
        for ($i = 0; $i < $count; $i++) {
            $entry = $ifd + 2 + $i * 12;
            if ($entry + 12 > strlen($exif)) {
                break;
            }
            if (unpack($short, $exif, $entry)[1] === 274
                && unpack($short, $exif, $entry + 2)[1] === 3
                && unpack($long, $exif, $entry + 4)[1] === 1) {
                return unpack($short, $exif, $entry + 8)[1];
            }
        }

        return 1;
    }

    private function orient(GdImage $image, int $orientation): GdImage
    {
        if (in_array($orientation, [2, 4, 5, 7], true)) {
            imageflip($image, $orientation === 4 ? IMG_FLIP_VERTICAL : IMG_FLIP_HORIZONTAL);
        }
        $angle = match ($orientation) {
            3 => 180,
            5, 8 => 90,
            6, 7 => -90,
            default => 0,
        };
        if ($angle === 0) {
            return $image;
        }
        $rotated = @imagerotate($image, $angle, imagecolorallocatealpha($image, 0, 0, 0, 127));
        if (! $rotated) {
            $this->reject('Nie udało się obrócić obrazu.');
        }
        imagesavealpha($rotated, true);

        return $rotated;
    }

    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['image' => $message]);
    }
}
