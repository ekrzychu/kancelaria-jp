<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Content\ArticleEditing;
use App\Content\ArticleStatus;
use App\Filament\Resources\ArticleResource;
use App\Models\Article;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

final class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected static ?string $title = 'Edytuj artykuł';

    protected Width|string|null $maxContentWidth = Width::FiveExtraLarge;

    #[Locked]
    public string $originalBodyHash;

    public function getSubheading(): ?string
    {
        return $this->record->status === ArticleStatus::Published
            ? 'Zapisane zmiany w opublikowanym artykule od razu pojawią się na stronie publicznej.'
            : null;
    }

    protected function getHeaderActions(): array
    {
        return [ArticleResource::previewAction()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->originalBodyHash = hash('sha256', $this->record->body_html);

        foreach (app(ArticleEditing::class)->parts($this->record) as $index => $part) {
            if (! $part['locked']) {
                $data['body_'.$index] = $part['html'];
            }
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data): Article {
            $current = Article::lockForUpdate()->findOrFail($record->getKey());
            Gate::authorize('update', $current);
            if (! hash_equals($this->originalBodyHash, hash('sha256', $current->body_html))) {
                throw ValidationException::withMessages(['data.title' => 'Treść zmieniła się od otwarcia formularza. Odśwież stronę.']);
            }

            $current->fill(Arr::only($data, ['slug', 'title', 'category', 'author', 'date', 'summary', 'image_alt']));
            $current->body_html = app(ArticleEditing::class)->saveBody($current, $data);
            $current->save();
            $this->originalBodyHash = hash('sha256', $current->body_html);
            $record->setRawAttributes($current->getAttributes(), sync: true);

            return $record;
        });
    }
}
