<?php

namespace App\Filament\Resources;

use App\Actions\ManageContent;
use App\Filament\Resources\SpecializationResource\Pages;
use App\Models\Specialization;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class SpecializationResource extends ManagedContentResource
{
    protected static ?string $model = Specialization::class;
    protected static ?string $slug = 'specjalizacje';
    protected static ?string $navigationLabel = 'Specjalizacje';
    protected static ?string $modelLabel = 'specjalizacja';
    protected static ?string $pluralModelLabel = 'specjalizacje';
    protected static ?int $navigationSort = 2;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;
    protected static ?string $recordTitleAttribute = 'title';

    protected const array FILTERS_BLOCKING_MOVES = ['category', 'active'];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label('Nazwa specjalizacji')->required()->maxLength(255)
                ->live(onBlur: true)->afterStateUpdated(function (?string $state, Get $get, Set $set, ?Specialization $record): void {
                    if ($record === null && blank($get('slug'))) {
                        $set('slug', Str::limit(Str::slug($state ?? ''), 191, ''));
                    }
                }),
            TextInput::make('slug')->label('Adres specjalizacji')->required()->maxLength(191)
                ->disabledOn('edit')->helperText('Adres pozostaje stały po zapisaniu.'),
            TextInput::make('category')->label('Kategoria')->required()->maxLength(255),
            Text::make(fn (?Specialization $record): string => $record?->active ? 'Status: Aktywna' : 'Status: Nieaktywna'),
            Textarea::make('summary')->label('Krótki opis')->required()->maxLength(16000)->columnSpanFull(),
            Textarea::make('home_summary')->label('Opis na stronie głównej')->maxLength(16000)->columnSpanFull()
                ->helperText('Gdy pole jest puste, na stronie głównej pojawi się krótki opis.'),
            Repeater::make('body')->label('Opis szczegółowy')->simple(Textarea::make('paragraph')
                ->label('Akapit')->required()->maxLength(16000))->minItems(1)->maxItems(100)->columnSpanFull()
                ->addActionLabel('Dodaj akapit'),
            Repeater::make('scope')->label('Zakres usług')->simple(TextInput::make('entry')
                ->label('Punkt zakresu')->required()->maxLength(2000))->minItems(1)->maxItems(100)->columnSpanFull()
                ->addActionLabel('Dodaj punkt'),
        ]);
    }

    public static function contentData(array $data, ?Model $record = null): array
    {
        $data = static::validateContent($data, [
            'title' => 'required|string|max:255',
            ...($record ? [] : ['slug' => ['required', 'string', 'max:191', 'regex:/\A[a-z0-9-]+\z/', Rule::unique('specializations', 'slug')]]),
            'category' => 'required|string|max:255',
            'summary' => 'required|string|max:16000',
            'home_summary' => 'nullable|string|max:16000',
            'body' => 'required|array|list|min:1|max:100',
            'body.*' => 'required|string|max:16000',
            'scope' => 'required|array|list|min:1|max:100',
            'scope.*' => 'required|string|max:2000',
        ]);
        $data['home_summary'] = filled($data['home_summary'] ?? null) ? $data['home_summary'] : null;

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
