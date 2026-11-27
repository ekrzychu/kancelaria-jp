<?php

namespace App\Media;

use App\Models\MediaImage;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use LogicException;
use Throwable;

final class ImageStorage
{
    public function __construct(private readonly ImageProcessor $processor) {}

    public function disk(): FilesystemAdapter
    {
        $root = str_replace('\\', '/', (string) config('media.root'));
        if (! preg_match('~\A(?:[A-Za-z]:)?/(?!/)~', $root) || preg_match('~(?:^|/)\.\.?(?:/|$)~', $root)) {
            throw new LogicException('MEDIA_STORAGE_ROOT must be an absolute path.');
        }

        $ancestor = $root;
        while (! is_dir($ancestor) && dirname($ancestor) !== $ancestor) {
            $ancestor = dirname($ancestor);
        }
        $real = realpath($ancestor);
        $public = realpath(public_path());
        $resolved = $real ? str_replace('\\', '/', $real).substr($root, strlen($ancestor)) : '';
        $public = $public ? str_replace('\\', '/', $public) : '';
        if ($resolved === '' || $public === '' || str_starts_with(strtolower(rtrim($resolved, '/').'/'), strtolower($public.'/'))) {
            throw new LogicException('Media storage must be outside public/.');
        }

        return Storage::build(['driver' => 'local', 'root' => $resolved, 'throw' => true]);
    }

    public function upload(UploadedFile $file, User $user, MediaPurpose $purpose): MediaImage
    {
        $image = $this->processor->process($file, $purpose);
        $current = $user->fresh();
        abort_unless($current && $current->session_version === $user->session_version
            && $purpose->allowed($current), 403);
        $media = new MediaImage();
        $media->forceFill([
            'id' => bin2hex(random_bytes(16)),
            'user_id' => $user->getKey(),
            'session_version' => $user->session_version,
            'purpose' => $purpose,
            'width' => $image['width'],
            'height' => $image['height'],
        ]);
        $disk = $this->disk();
        try {
            $disk->put($media->filename(), $image['bytes']);
            $media->save();
        } catch (Throwable $exception) {
            rescue(fn () => $disk->delete($media->filename()));

            throw $exception;
        }

        return $media;
    }
}
