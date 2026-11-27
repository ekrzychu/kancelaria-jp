<?php

namespace App\Filament\Resources;

use App\Actions\ManageContent;
use App\Content\JobStatus;
use App\Filament\Resources\JobOfferResource\Pages;
use App\Models\JobOffer;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
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
    protected static ?string $recordTitleAttribute = 'title';

    protected const array FILTERS_BLOCKING_MOVES = ['status'];

    private const array STATUSES = ['draft' => 'Szkic', 'open' => 'Aktywne', 'closed' => 'Zakończone'];
    private const int TITLE_LENGTH = 255;
    private const int DESCRIPTION_LENGTH = 16000;
    private const int URL_LENGTH = 2048;

    public static function form(Schema $schema): Schema
    {
        $facts = [];
        foreach (JobOffer::FACTS as $field => $label) {
            $facts[] = $field === 'deadline'
                ? DatePicker::make($field)->label($label)
                : TextInput::make($field)->label($label)->maxLength(255);
        }

        $lists = [];
        foreach (JobOffer::LISTS as $field => $label) {
            $lists[] = Repeater::make($field)->label($label)->simple(
                Textarea::make('punkt')->required()->string()->maxLength(2000)->rows(2),
            )->defaultItems(0)->maxItems(30)->addActionLabel('Dodaj punkt');
        }

        return $schema->components([
            Section::make('Podstawowe informacje')->columnSpanFull()->columns(2)->schema([
                TextInput::make('title')->label('Nazwa stanowiska')->required()->maxLength(self::TITLE_LENGTH),
                TextInput::make('slug')->label('Adres ogłoszenia')->prefix('/kariera/')->disabled()
                    ->placeholder('Powstanie z nazwy przy pierwszym zapisie'),
                Text::make(fn (?JobOffer $record): string => 'Status: '.self::STATUSES[($record?->status ?? JobStatus::Draft)->value])->columnSpanFull(),
                Textarea::make('description')->label('Krótki opis')->rows(3)->maxLength(self::DESCRIPTION_LENGTH)->columnSpanFull(),
                ...$facts,
            ]),
            Section::make('Treść ogłoszenia')->columnSpanFull()->columns(2)->schema([
                Textarea::make('body')->label('Opis stanowiska')->rows(5)->maxLength(self::DESCRIPTION_LENGTH)->columnSpanFull(),
                ...$lists,
            ]),
            Section::make('Aplikowanie')->columnSpanFull()->columns(2)->schema([
                Textarea::make('application_instructions')->label(JobOffer::APPLICATION_TEXT['application_instructions'])->rows(3)->maxLength(self::DESCRIPTION_LENGTH)->columnSpanFull(),
                Textarea::make('application_documents')->label(JobOffer::APPLICATION_TEXT['application_documents'])->rows(3)->maxLength(self::DESCRIPTION_LENGTH)->columnSpanFull(),
                TextInput::make('application_email')->label('Adres e-mail do składania aplikacji')->email()->maxLength(255),
                TextInput::make('url')->label('Opcjonalny zewnętrzny link aplikacyjny')->maxLength(self::URL_LENGTH)
                    ->helperText('Opcjonalny adres HTTP lub HTTPS do składania aplikacji.'),
                Textarea::make('candidate_privacy')->label(JobOffer::APPLICATION_TEXT['candidate_privacy'])->rows(4)->maxLength(self::DESCRIPTION_LENGTH)->columnSpanFull(),
            ]),
        ]);
    }

    public static function contentData(array $data, ?Model $record = null): array
    {
        $url = $data['url'] ?? null;
        $legacyLink = $record && is_string($url) && $record->url === $url && JobOffer::legacyLink($url);
        $rules = [
            'title' => 'required|string|max:'.self::TITLE_LENGTH,
            'application_email' => 'nullable|string|email:filter|max:255',
            'url' => ['nullable', 'string', 'max:'.self::URL_LENGTH, ...($legacyLink ? [] : ['url:http,https'])],
        ];
        foreach (array_keys(JobOffer::FACTS) as $field) {
            $rules[$field] = $field === 'deadline' ? 'nullable|date_format:Y-m-d' : 'nullable|string|max:255';
        }
        foreach (['description', 'body', ...array_keys(JobOffer::APPLICATION_TEXT)] as $field) {
            $rules[$field] = 'nullable|string|max:'.self::DESCRIPTION_LENGTH;
        }
        foreach (array_keys(JobOffer::LISTS) as $field) {
            $rules[$field] = 'nullable|array|list|max:30';
            $rules[$field.'.*'] = 'required|string|max:2000';
        }

        return static::validateContent($data, $rules);
    }

    public static function table(Table $table): Table
    {
        return static::directoryTable($table->columns([
            TextColumn::make('title')->extraAttributes(['class' => 'panel-record-title'])->label('Nazwa stanowiska')->searchable()->wrap()->placeholder('—'),
            TextColumn::make('status')->label('Status')->extraAttributes(['class' => 'panel-table-status'])->formatStateUsing(fn (JobStatus $state): string => self::STATUSES[$state->value])
                ->color(fn (JobStatus $state): string => $state === JobStatus::Open ? 'success' : 'gray'),
            TextColumn::make('display_order')->label('Kolejność'),
            TextColumn::make('updated_at')->label('Data ostatniej modyfikacji')->dateTime('d.m.Y, H:i', timezone: 'Europe/Warsaw'),
        ]), [
            SelectFilter::make('status')->label('Status')->options(self::STATUSES),
        ], array_map(fn (Action $action): Action => $action->link(), self::publicationActions()));
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
                        $current->fill($form ? $form($current) : static::contentData(array_replace($current->attributesToArray(), ['deadline' => $current->deadline?->toDateString()]), $current));
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
