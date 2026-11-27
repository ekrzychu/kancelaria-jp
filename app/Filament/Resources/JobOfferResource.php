<?php

namespace App\Filament\Resources;

use App\Actions\ManageContent;
use App\Content\JobStatus;
use App\Filament\Resources\JobOfferResource\Pages;
use App\Models\JobOffer;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class JobOfferResource extends ManagedContentResource
{
    protected static ?string $model = JobOffer::class;
    protected static ?string $slug = 'kariera';
    protected static ?string $navigationLabel = 'Kariera';
    protected static ?string $modelLabel = 'oferta pracy';
    protected static ?string $pluralModelLabel = 'oferty pracy';
    protected static ?int $navigationSort = 4;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;
    protected static ?string $recordTitleAttribute = 'title';

    protected const array FILTERS_BLOCKING_MOVES = ['status'];

    private const array STATUSES = ['draft' => 'Szkic', 'open' => 'Aktywne', 'closed' => 'Zakończone'];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label('Nazwa stanowiska')->required()->maxLength(255),
            Text::make(fn (?JobOffer $record): string => 'Status: '.self::STATUSES[($record?->status ?? JobStatus::Draft)->value]),
            Textarea::make('description')->label('Opis ogłoszenia')->rows(5)->maxLength(16000)->columnSpanFull(),
            TextInput::make('url')->label('Link do ogłoszenia')->maxLength(2048)->columnSpanFull()
                ->helperText('Nowe odnośniki muszą używać HTTP lub HTTPS.'),
        ]);
    }

    public static function contentData(array $data, ?Model $record = null): array
    {
        $url = $data['url'] ?? null;
        // Preserve supported links from the content import if the editor leaves them unchanged.
        $legacyLink = $record && is_string($url) && $record->url === $url
            && preg_match('~\A(?:/(?!/)[^\x00-\x20\\\\]*|\#[^\s]*|mailto:[^\s]+|tel:[+0-9-]+)\z~i', $url);

        return static::validateContent($data, [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:16000',
            'url' => $legacyLink ? 'nullable|string|max:2048' : 'nullable|string|max:2048|url:http,https',
        ]);
    }

    public static function table(Table $table): Table
    {
        return static::directoryTable($table->columns([
            TextColumn::make('title')->label('Nazwa stanowiska')->searchable()->wrap()->placeholder('—'),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (JobStatus $state): string => self::STATUSES[$state->value])
                ->color(fn (JobStatus $state): string => $state === JobStatus::Open ? 'success' : 'gray'),
            TextColumn::make('display_order')->label('Kolejność'),
            TextColumn::make('updated_at')->label('Data ostatniej modyfikacji')->dateTime('d.m.Y, H:i', timezone: 'Europe/Warsaw'),
        ]), [
            SelectFilter::make('status')->label('Status')->options(self::STATUSES),
        ], self::publicationActions());
    }

    public static function publicationActions(?Closure $form = null): array
    {
        return [
            self::stateAction('publish', 'Opublikuj', JobStatus::Draft, JobStatus::Open, $form),
            self::stateAction('close', 'Zakończ rekrutację', JobStatus::Open, JobStatus::Closed),
            self::stateAction('reopen', 'Wznów rekrutację', JobStatus::Closed, JobStatus::Open, $form),
        ];
    }

    private static function stateAction(string $action, string $label, JobStatus $from, JobStatus $to, ?Closure $form = null): Action
    {
        $opens = $to === JobStatus::Open;

        return Action::make($action)->label($label)->button()->color($opens ? 'primary' : 'gray')
            ->authorize('update')->visible(fn (JobOffer $record): bool => ! $record->trashed() && $record->status === $from)
            ->requiresConfirmation()->modalSubmitActionLabel($label)
            ->modalDescription($opens ? 'Oferta pojawi się na stronie publicznej.' : 'Oferta zniknie ze strony publicznej.')
            ->action(function (JobOffer $record) use ($from, $to, $opens, $form): void {
                $current = app(ManageContent::class)->change($record, 'update', function (JobOffer $current) use ($from, $to, $opens, $form): void {
                    if ($current->status !== $from) {
                        return;
                    }

                    if ($opens) {
                        $current->fill($form ? $form($current) : static::contentData($current->only(['title', 'description', 'url']), $current));
                    }
                    $current->status = $to;
                    $current->save();
                });
                $record->setRawAttributes($current->getAttributes(), sync: true);
            });
    }

    protected static function beforeRestore(Model $record): void
    {
        if ($record->status === JobStatus::Open) {
            $record->status = JobStatus::Closed;
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobOffers::route('/'),
            'create' => Pages\CreateJobOffer::route('/dodaj'),
            'edit' => Pages\EditJobOffer::route('/{record}/edytuj'),
        ];
    }
}
