<?php

namespace App\Filament\Resources;

use App\Actions\ManageContent;
use App\Filament\Resources\SpecializationResource\Pages;
use App\Models\Specialization;
use App\Support\PublicUrl;
use BackedEnum;
use UnitEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

final class SpecializationResource extends ManagedContentResource
{
    protected static ?string $model = Specialization::class;
    protected static ?string $slug = 'specjalizacje';
    protected static ?string $navigationLabel = 'Specjalizacje';
    protected static ?string $modelLabel = 'specjalizacja';
    protected static ?string $pluralModelLabel = 'specjalizacje';
    protected static string|UnitEnum|null $navigationGroup = 'Treść';
    protected static ?int $navigationSort = 2;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;
    protected static ?string $recordTitleAttribute = 'title';

    protected const array FILTERS_BLOCKING_MOVES = ['category', 'active'];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label('Nazwa specjalizacji')->required()->string()->maxLength(255),
            TextInput::make('slug')->label('Adres specjalizacji')->rules(['nullable', 'string'])->maxLength(191)
                ->visible(fn (): bool => auth()->user()->isActiveAdministrator())->disabledOn('edit')
                ->helperText('Puste pole oznacza adres z nazwy. Zapisany adres pozostaje stały.'),
            TextInput::make('category')->label('Kategoria')->required()->string()->maxLength(255),
            Text::make(fn (?Specialization $record): string => $record?->active ? 'Status: Aktywna' : 'Status: Nieaktywna'),
            Toggle::make('homepage_featured')->label('Na stronie głównej')->default(false)->rule('boolean')
                ->helperText('Strona główna pokazuje do ośmiu wybranych aktywnych specjalizacji według kolejności.'),
            Textarea::make('summary')->label('Krótki opis')->required()->string()->maxLength(16000)->columnSpanFull(),
            Textarea::make('home_summary')->label('Opis na stronie głównej')->rules(['nullable', 'string'])->maxLength(16000)->columnSpanFull()
                ->helperText('Gdy pole jest puste, na stronie głównej pojawi się krótki opis.'),
            Repeater::make('body')->label('Opis szczegółowy')->simple(Textarea::make('paragraph')
                ->label('Akapit')->required()->string()->maxLength(16000))->minItems(1)->maxItems(100)->columnSpanFull()
                ->addActionLabel('Dodaj akapit'),
            Repeater::make('scope')->label('Zakres usług')->simple(TextInput::make('entry')
                ->label('Punkt zakresu')->required()->string()->maxLength(2000))->minItems(1)->maxItems(100)->columnSpanFull()
                ->addActionLabel('Dodaj punkt'),
        ]);
    }

    public static function contentData(array $data, ?Model $record = null): array
    {
        // Check uniqueness while holding the write lock and require ordered lists after dehydration.
        $data = static::validateContent($data, [
            ...(! $record && auth()->user()->isActiveAdministrator()
                ? ['slug' => ['nullable', 'regex:/\A[a-z0-9-]+\z/', Rule::unique('specializations', 'slug')]] : []),
            'body' => 'list',
            'scope' => 'list',
        ]) + Arr::only($data, ['title', 'category', 'summary', 'home_summary', 'homepage_featured']);
        $data['home_summary'] = filled($data['home_summary'] ?? null) ? $data['home_summary'] : null;
        if (! $record && blank($data['slug'] ?? null)) {
            $data['slug'] = PublicUrl::availableSlug($data['title'], Specialization::class);
        }

        return $record ? $data : $data + ['active' => false];
    }

    public static function table(Table $table): Table
    {
        return static::directoryTable($table->columns([
            TextColumn::make('title')->label('Nazwa specjalizacji')->searchable()->wrap(),
            TextColumn::make('category')->label('Kategoria')->searchable()->wrap(),
            TextColumn::make('active')->label('Status')->badge()
                ->formatStateUsing(fn (bool $state): string => $state ? 'Aktywna' : 'Nieaktywna')
                ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
            TextColumn::make('display_order')->label('Kolejność'),
            IconColumn::make('homepage_featured')->label('Na stronie głównej')->boolean(),
        ]), [
            SelectFilter::make('category')->label('Kategoria')->options(fn (): array => Specialization::query()
                ->distinct()->orderBy('category')->pluck('category', 'category')->all()),
            TernaryFilter::make('active')->label('Status')->trueLabel('Aktywne')->falseLabel('Nieaktywne'),
        ], [self::stateAction(true), self::stateAction(false)]);
    }

    private static function stateAction(bool $active): Action
    {
        return Action::make($active ? 'activate' : 'deactivate')
            ->label($active ? 'Aktywuj' : 'Dezaktywuj')->button()->color($active ? 'primary' : 'gray')
            ->authorize('update')->visible(fn (Specialization $record): bool => ! $record->trashed() && $record->active !== $active)
            ->requiresConfirmation()
            ->modalDescription($active ? 'Specjalizacja pojawi się na stronie publicznej.' : 'Specjalizacja zniknie ze strony publicznej.')
            ->action(fn (Specialization $record) => app(ManageContent::class)->change($record, 'update',
                fn (Specialization $current) => $current->update(['active' => $active])));
    }

    protected static function beforeRestore(Model $record): void
    {
        $record->active = false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSpecializations::route('/'),
            'create' => Pages\CreateSpecialization::route('/dodaj'),
            'edit' => Pages\EditSpecialization::route('/{record}/edytuj'),
        ];
    }
}
