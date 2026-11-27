<?php

namespace App\Media;

use App\Content\ArticleFigures;
use App\Models\Article;
use App\Models\MediaImage;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class MediaReferences
{
    public const END = 10000;

    public function __construct(private readonly ImageStorage $storage) {}

    public function saveArticle(Article $article, array $input): void
    {
        $validated = Validator::make(['data' => $input], [
            'data.image_blocks' => 'present|array|list|max:20',
            'data.image_blocks.*' => 'array:image,alt,caption,position',
            'data.image_blocks.*.image' => ['required', 'string', 'regex:/\A[a-f0-9]{32}\z/'],
            'data.image_blocks.*.alt' => 'required|string|max:300',
            'data.image_blocks.*.caption' => 'nullable|string|max:1000',
            'data.image_blocks.*.position' => 'required|integer|min:0|max:'.self::END,
        ], [
            'present' => 'Sprawdź listę zdjęć.',
            'array' => 'Sprawdź listę zdjęć.',
            'list' => 'Sprawdź kolejność zdjęć.',
            'required' => 'Uzupełnij zdjęcie, opis alternatywny i położenie.',
            'string' => 'Wpisz poprawny tekst.',
            'regex' => 'Wybierz poprawne zdjęcie.',
            'max' => 'Przekroczono dopuszczalną długość lub liczbę zdjęć.',
            'min' => 'Wybierz poprawne położenie.',
            'integer' => 'Wybierz poprawne położenie.',
        ])->validate();

        $blocks = $validated['data']['image_blocks'];
        // Lock in a predictable order when a form submits more than one upload.
        $ids = array_unique(array_column($blocks, 'image'));
        sort($ids);
        foreach ($ids as $id) {
            $this->bind($id, MediaPurpose::Article, $article, 'data.image_blocks');
        }
        $positions = $blocks === [] ? [] : app(ArticleFigures::class)->positions($article);
        $article->image_blocks = array_map(static function (array $block) use ($positions): array {
            $index = (int) $block['position'];
            $block['position'] = isset($positions[$index]) ? $index : self::END;

            return $block;
        }, $blocks);
    }

    public function bind(string $id, MediaPurpose $purpose, Model $record, string $field): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User && $purpose->allowed($user), 403);
        abort_if($purpose === MediaPurpose::Avatar && ! $user->is($record), 403);
        $valid = preg_match('/\A[a-f0-9]{32}\z/', $id);
        $media = $valid ? MediaImage::lockForUpdate()->find($id) : null;
        if (! $media || $media->purpose !== $purpose
            || ($media->record_id === null ? ! $this->ownsPending($media, $user) : $media->record_id !== $record->getKey())
            || ! $this->storage->disk()->exists($media->filename())) {
            throw ValidationException::withMessages([$field => 'Zdjęcie nie jest dostępne. Wybierz plik ponownie.']);
        }
        if ($media->record_id === null) {
            $media->record_id = $record->getKey();
            $media->save();
        }
    }

    public function mayView(MediaImage $media, ?User $user): bool
    {
        if ($user && ! $media->purpose->allowed($user)) {
            return false;
        }
        if ($media->record_id === null) {
            return $user !== null && $this->ownsPending($media, $user);
        }

        if ($media->purpose === MediaPurpose::Avatar) {
            return $user !== null && $media->user_id === $user->getKey() && $media->record_id === $user->getKey()
                && $user->avatar_media_id === $media->id;
        }

        if ($media->purpose === MediaPurpose::Portrait) {
            $team = TeamMember::find($media->record_id);

            return $team && $team->portrait_media_id === $media->id
                && ($user ? Gate::forUser($user)->allows('update', $team) : $team->portrait_source === 'uploaded');
        }

        $article = ($user ? Article::query() : Article::published())->find($media->record_id);

        return $article && in_array($media->id, array_column($article->image_blocks ?? [], 'image'), true)
            && (! $user || Gate::forUser($user)->allows('view', $article));
    }

    private function ownsPending(MediaImage $media, User $user): bool
    {
        return $media->user_id === $user->getKey() && $media->session_version === $user->session_version
            && $media->created_at->gt(now()->subHours(config('media.temporary_hours')));
    }
}
