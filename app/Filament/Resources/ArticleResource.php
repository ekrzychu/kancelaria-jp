<?php

namespace App\Filament\Resources;

use App\Content\ArticleEditing;
use App\Content\ArticleHtml;
use App\Content\ArticleStatus;
use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
use App\Support\PublicUrl;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

final class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;
    protected static ?string $slug = 'artykuly';
    protected static ?string $navigationLabel = 'Artykuły';
    protected static ?string $modelLabel = 'artykuł';
    protected static ?string $pluralModelLabel = 'artykuły';
    protected static ?int $navigationSort = 1;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static ?string $recordTitleAttribute = 'title';

    private const STATUSES = [
        'draft' => 'Szkic',
        'published' => 'Opublikowany',
        'unpublished' => 'Wycofany',
    ];

    public static function form(Schema $schema): Schema
    {
        return $schema->components(function (?Article $record): array {
            $fields = [
                TextInput::make('title')->label('Tytuł')->required()->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Get $get, Set $set, ?Article $record): void {
                        if ($record === null && blank($get('slug'))) {
                            $set('slug', Str::limit(Str::slug($state ?? ''), 191, ''));
                        }
                    }),
                TextInput::make('slug')->label('Adres artykułu')->required()->maxLength(191)
                    ->unique(ignoreRecord: true)
                    ->rule(fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                        if (! PublicUrl::isArticleSlug($value)) {
                            $fail('Użyj wolnego adresu zawierającego małe litery, cyfry i łączniki.');
                        }
                    })
                    ->disabled(fn (?Article $record): bool => $record && $record->status !== ArticleStatus::Draft)
                    ->helperText('Adres pozostaje stały po pierwszej publikacji.'),
                TextInput::make('category')->label('Kategoria')->maxLength(255),
                TextInput::make('author')->label('Autor')->maxLength(255),
                DatePicker::make('date')->label('Data publikacji')->format('Y-m-d')
                    ->rules(['date_format:Y-m-d'])->minDate('1000-01-01')->maxDate('9999-12-31'),
                TextInput::make('image_alt')->label('Opis istniejącego zdjęcia')->maxLength(255)
                    ->visible(fn (?Article $record): bool => filled($record?->image)),
                Textarea::make('summary')->label('Streszczenie')->rows(2)->maxLength(15_000)->columnSpanFull(),
            ];

            foreach (app(ArticleEditing::class)->parts($record) as $index => $part) {
                $fields[] = $part['locked']
                    ? Text::make(new HtmlString($part['html']))->columnSpanFull()
                    : RichEditor::make('body_'.$index)->label('Treść artykułu')->columnSpanFull()
                        ->maxLength(ArticleHtml::MAX_LENGTH)->fileAttachments(false)
                        ->toolbarButtons([
                            ['bold', 'italic'], ['h2', 'h3'], ['bulletList', 'orderedList'],
                            ['blockquote', 'link', 'table'], ['undo', 'redo'],
                        ]);
            }

            return $fields;
        });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withoutGlobalScope(SoftDeletingScope::class))
            ->columns([
                TextColumn::make('title')->label('Tytuł')->searchable()->wrap(),
                TextColumn::make('category')->label('Kategoria')->placeholder('—'),
                TextColumn::make('author')->label('Autor')->searchable()->placeholder('—'),
                TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (ArticleStatus $state): string => self::STATUSES[$state->value])
                    ->color(fn (ArticleStatus $state): string => $state === ArticleStatus::Published ? 'success' : 'gray'),
                TextColumn::make('date')->label('Data publikacji')->date('d.m.Y', timezone: 'UTC')
                    ->placeholder('—')->sortable(),
            ])
            ->stackedOnMobile()
            ->defaultSort('date', 'desc')
            ->filters([
                SelectFilter::make('status')->label('Status publikacji')->options(self::STATUSES),
                TrashedFilter::make()->label('Usunięte artykuły'),
            ])
            ->recordActions([
                EditAction::make()->label('Edytuj')->icon(null)->hidden(fn (Article $record): bool => $record->trashed()),
                self::previewAction()->hidden(fn (Article $record): bool => $record->trashed()),
                self::publicationAction(ArticleStatus::Published),
                self::publicationAction(ArticleStatus::Unpublished),
                ActionGroup::make([
                    DeleteAction::make()->label('Przenieś do kosza')
                        ->modalDescription('Artykuł zniknie ze strony publicznej i będzie można go przywrócić.'),
                    RestoreAction::make()->label('Przywróć')
                        ->using(function (Article $record): bool {
                            Gate::authorize('restore', $record);

                            return DB::transaction(function () use ($record): bool {
                                $current = Article::onlyTrashed()->lockForUpdate()->findOrFail($record->id);
                                if ($current->status === ArticleStatus::Published) {
                                    $current->status = ArticleStatus::Unpublished;
                                }

                                return $current->restore();
                            });
                        }),
                ])->label('Więcej')->button()->color('gray'),
            ]);
    }

    public static function previewAction(): Action
    {
        return Action::make('preview')->label('Podgląd')
            ->authorize('view')
            ->url(fn (Article $record): string => route('filament.admin.articles.preview', ['article' => $record]))
            ->openUrlInNewTab();
    }

    public static function publicationAction(ArticleStatus $status, ?Closure $saveForm = null): Action
    {
        $publish = $status === ArticleStatus::Published;
        $label = fn (Article $record): string => match (true) {
            ! $publish => 'Wycofaj publikację',
            $record->status === ArticleStatus::Unpublished => 'Opublikuj ponownie',
            default => 'Opublikuj',
        };

        return Action::make($publish ? 'publish' : 'unpublish')->label($label)
            ->button()->color($publish ? 'primary' : 'gray')
            ->visible(fn (Article $record): bool => ! $record->trashed() && ($publish
                ? $record->status !== ArticleStatus::Published
                : $record->status === ArticleStatus::Published))
            ->authorize('publish')->requiresConfirmation()
            ->modalSubmitActionLabel($label)
            ->modalDescription(match (true) {
                ! $publish => 'Artykuł zniknie ze strony publicznej.',
                $saveForm !== null => 'Zmiany zostaną zapisane, a artykuł pojawi się na stronie publicznej.',
                default => 'Artykuł pojawi się na stronie publicznej.',
            })
            ->action(function (Article $record) use ($publish, $status, $saveForm): void {
                $current = DB::transaction(function () use ($record, $publish, $status, $saveForm): Article {
                    $current = Article::lockForUpdate()->findOrFail($record->id);
                    Gate::authorize('publish', $current);
                    if ($publish ? $current->status !== ArticleStatus::Published : $current->status === ArticleStatus::Published) {
                        if ($publish) {
                            $saveForm?->__invoke($current);
                            $current->date ??= now('Europe/Warsaw')->toDateString();
                        }
                        $current->status = $status;
                        $current->save();
                    }

                    return $current;
                });
                $record->setRawAttributes($current->getAttributes(), sync: true);
            })->successNotificationTitle($publish ? 'Artykuł opublikowany' : 'Publikacja wycofana');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/dodaj'),
            'edit' => Pages\EditArticle::route('/{record}/edytuj'),
        ];
    }
}
