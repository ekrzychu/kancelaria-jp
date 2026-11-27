<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Content\ArticleEditing;
use App\Content\ArticleStatus;
use App\Filament\Resources\ArticleResource;
use App\Media\MediaReferences;
use App\Models\Article;
use Filament\Actions\Action;
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

    protected Width|string|null $maxContentWidth = Width::Full;

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
        return [
            ArticleResource::embeddedPreviewAction(),
            ArticleResource::publicationAction(ArticleStatus::Published, function (Article $record): void {
                try {
                    $this->handleRecordUpdate($record, $this->form->getState());
                } catch (ValidationException $exception) {
                    $this->unmountAction(cancelParentActions: false);

                    throw $exception;
                }
            })->after(function (): void {
                $this->rememberData();
                $this->redirect(ArticleResource::getUrl());
            }),
            ArticleResource::publicationAction(ArticleStatus::Unpublished),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return ArticleResource::getUrl();
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->label('Zapisz zmiany');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->originalBodyHash = $this->contentHash($this->record);
        $data['image_blocks'] ??= [];

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
            if (! hash_equals($this->originalBodyHash, $this->contentHash($current))) {
                throw ValidationException::withMessages(['data.title' => 'Treść zmieniła się od otwarcia formularza. Odśwież stronę.']);
            }

            $current->fill(Arr::only($data, ['title', 'category', 'author', 'date', 'summary', 'image_alt']));
            if ($current->status === ArticleStatus::Draft && auth()->user()->isActiveAdministrator() && isset($data['slug'])) {
                $current->slug = $data['slug'];
            }
            $current->body_html = app(ArticleEditing::class)->saveBody($current, $data);
            app(MediaReferences::class)->saveArticle($current, $data + ['image_blocks' => []]);
            $current->save();
            $this->originalBodyHash = $this->contentHash($current);
            $record->setRawAttributes($current->getAttributes(), sync: true);

            return $record;
        });
    }

    private function contentHash(Article $article): string
    {
        return hash('sha256', serialize([$article->body_html, $article->image_blocks ?? []]));
    }
}
