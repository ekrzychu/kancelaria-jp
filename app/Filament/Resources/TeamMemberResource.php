<?php

namespace App\Filament\Resources;

use App\Content\TeamGroup;
use App\Filament\Resources\TeamMemberResource\Pages;
use App\Models\TeamMember;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

final class TeamMemberResource extends ManagedContentResource
{
    protected static ?string $model = TeamMember::class;
    protected static ?string $slug = 'zespol';
    protected static ?string $navigationLabel = 'Zespół';
    protected static ?string $modelLabel = 'profil zespołu';
    protected static ?string $pluralModelLabel = 'profile zespołu';
    protected static ?int $navigationSort = 3;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;
    protected static ?string $recordTitleAttribute = 'name';

    private static function groups(): array
    {
        return array_column(array_map(fn (TeamGroup $group): array => [$group->value, $group->heading()], TeamGroup::cases()), 1, 0);
    }

    private static function portraits(?Model $record = null): array
    {
        $options = config('site.team_portraits', []);
        if (filled($record?->portrait)) {
            $options[$record->portrait] ??= 'Dotychczasowe zdjęcie';
        }

        return array_filter($options, fn (string $filename): bool => preg_match('/\A[\w.-]+\.(?:avif|jpe?g|png|webp)\z/i', $filename)
            && is_file(public_path('assets/img/'.$filename)), ARRAY_FILTER_USE_KEY);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Imię i nazwisko')->required()->maxLength(255),
            TextInput::make('role')->label('Stanowisko')->required()->maxLength(255),
            Select::make('team_group')->label('Grupa')->options(self::groups())->required(),
            Select::make('portrait')->label('Zdjęcie')->options(fn (?TeamMember $record): array => self::portraits($record))
                ->placeholder('Bez zdjęcia')->helperText('Wybierz zatwierdzony portret lub pozostaw pole puste.'),
            Toggle::make('homepage_partner')->label('Wspólnik na stronie głównej')->default(false)
                ->helperText('Strona główna pokazuje maksymalnie dwa wyróżnione profile według kolejności.'),
        ]);
    }

    public static function contentData(array $data, ?Model $record = null): array
    {
        return static::validateContent($data, [
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'team_group' => ['required', Rule::enum(TeamGroup::class)],
            'portrait' => ['nullable', 'string', Rule::in(array_keys(self::portraits($record)))],
            'homepage_partner' => 'required|boolean',
        ]);
    }

    public static function table(Table $table): Table
    {
        return static::directoryTable($table->columns([
            ImageColumn::make('portrait')->label('Zdjęcie')->circular()
                ->getStateUsing(fn (TeamMember $record): ?string => isset(self::portraits($record)[$record->portrait])
                    ? rtrim(config('hosts.admin_url'), '/').'/assets/img/'.$record->portrait : null),
            TextColumn::make('name')->label('Imię i nazwisko')->searchable()->wrap(),
            TextColumn::make('role')->label('Stanowisko')->searchable()->wrap(),
            TextColumn::make('team_group')->label('Grupa')->formatStateUsing(fn (TeamGroup $state): string => $state->heading()),
            IconColumn::make('homepage_partner')->label('Wspólnik na stronie głównej')->boolean(),
            TextColumn::make('display_order')->label('Kolejność'),
        ]), [
            SelectFilter::make('team_group')->label('Grupa')->options(self::groups()),
        ])->defaultSort(fn (Builder $query): Builder => $query->orderBy('team_group')->orderBy('display_order')->orderBy('id'));
    }

    protected static function restoreDescription(): string
    {
        return 'Profil ponownie pojawi się na stronie publicznej i może wrócić na stronę główną.';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeamMembers::route('/'),
            'create' => Pages\CreateTeamMember::route('/dodaj'),
            'edit' => Pages\EditTeamMember::route('/{record}/edytuj'),
        ];
    }
}
