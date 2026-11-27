<?php

namespace App\Http\Controllers;

use App\Media\ImageStorage;
use App\Media\MediaPurpose;
use App\Media\MediaReferences;
use App\Models\MediaImage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class MediaController
{
    public function __construct(private readonly ImageStorage $storage, private readonly MediaReferences $references) {}

    public function upload(Request $request, MediaPurpose $purpose): JsonResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User && $purpose->allowed($user), 403);
        $request->validate(['image' => ['required', 'file', 'max:'.config('media.max_kilobytes')]], [
            'image.required' => 'Wybierz plik zdjęcia.',
            'image.file' => 'Nie udało się przesłać pliku zdjęcia.',
            'image.max' => 'Zdjęcie przekracza dozwolony rozmiar.',
            'image.uploaded' => 'Nie udało się przesłać zdjęcia. Sprawdź rozmiar pliku.',
        ]);

        $media = $this->storage->upload($request->file('image'), $user, $purpose);

        return response()->json(['id' => $media->id], 201);
    }

    public function publicImage(string $media): BinaryFileResponse
    {
        return $this->deliver($media, null);
    }

    public function privateImage(Request $request, string $media): BinaryFileResponse
    {
        abort_unless($request->user() instanceof User, 403);

        return $this->deliver($media, $request->user());
    }

    private function deliver(string $id, ?User $user): BinaryFileResponse
    {
        $media = MediaImage::findOrFail($id);
        abort_unless($this->references->mayView($media, $user), 404);
        $file = $this->storage->disk()->path($media->filename());
        abort_unless(is_file($file) && ! is_link($file), 404);

        return response()->file($file, [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
