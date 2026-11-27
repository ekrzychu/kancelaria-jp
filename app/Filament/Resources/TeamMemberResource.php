<?php

namespace App\Filament\Resources;

use App\Content\TeamGroup;
use App\Filament\Forms\ImageUpload;
use App\Filament\Resources\TeamMemberResource\Pages;
use App\Media\MediaPurpose;
use App\Media\MediaReferences;
use App\Models\TeamMember;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

final class TeamMemberResource extends ManagedContentResource
{
    protected static ?string $model = TeamMember::class;
    protected static ?string $slug = 'zespol';
    protected static ?string $navigationLabel = 'Zespół';
    protected static ?string $modelLabel = 'profil zespołu';
    protected static ?string $pluralModelLabel = 'profile zespołu';
    protected static ?int $navigationSort = 3;
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

        return array_filter($options, fn (string $filename): bool => TeamMember::existingPortraitUrl($filename) !== null, ARRAY_FILTER_USE_KEY);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Imię i nazwisko')->required()->string()->maxLength(255),
            TextInput::make('role')->label('Stanowisko')->required()->string()->maxLength(255),
            Select::make('team_group')->label('Grupa')->options(self::groups())->required(),
            Select::make('portrait_selection')->label('Zdjęcie na stronie')->required()->default('none')->selectablePlaceholder(false)
                ->options(fn (Get $get, ?TeamMember $record): array => ['none' => 'Brak zdjęcia']
                    + (filled($get('portrait_media_id')) ? ['uploaded' => 'Przesłane zdjęcie'] : [])
                    + self::portraits($record))
                ->helperText('Wybranie opcji „Brak zdjęcia” zachowuje plik w panelu.'),
            ImageUpload::make('portrait_media_id')->label('Przesłane zdjęcie (podgląd prywatny)')->purpose(MediaPurpose::Portrait)->live()
                ->afterStateUpdated(function (mixed $state, Get $get, Set $set): void {
                    if (blank($state) && $get('portrait_selection') === 'uploaded') {
                        $set('portrait_selection', 'none');
                    }
                })
                ->helperText('Aby opublikować plik, wybierz „Przesłane zdjęcie” w polu „Zdjęcie na stronie”. Zapisz profil po usunięciu lub zastąpieniu pliku.'),
            Toggle::make('homepage_partner')->label('Wspólnik na stronie głównej')->default(false)
                ->rules(['required', 'boolean'])
                ->helperText('Strona główna pokazuje maksymalnie dwa wyróżnione profile według kolejności.'),
        ]);
    }

    public static function contentData(array $data, ?Model $record = null): array
    {
        return Arr::only($data, ['name', 'role', 'team_group', 'homepage_partner']);
    }

    public static function savePortrait(TeamMember $member, array $data): void
    {
        $id = $data['portrait_media_id'] ?? null;
        if ($id !== null) {
            if (! is_string($id)) {
                throw ValidationException::withMessages(['data.portrait_media_id' => 'Wybierz poprawne zdjęcie.']);
            }
            app(MediaReferences::class)->bind($id, MediaPurpose::Portrait, $member, 'data.portrait_media_id');
        }
        $selection = $data['portrait_selection'] ?? 'none';
        if ($selection === 'uploaded' && $id !== null) {
            $member->portrait_source = 'uploaded';
        } elseif ($selection === 'none' || ($selection === 'uploaded' && $id === null)) {
            $member->portrait_source = 'none';
        } elseif (is_string($selection) && isset(self::portraits($member)[$selection])) {
            $member->portrait_source = 'existing';
            $member->portrait = $selection;
        } else {
            throw ValidationException::withMessages(['data.portrait_selection' => 'Wybierz dostępne zdjęcie.']);
        }
        $member->portrait_media_id = $id;
        $member->save();
    }

    private static function portraitUrl(TeamMember $record): ?string
    {
        if ($record->trashed()) {
            return null;
        }

        $path = $record->publicPortraitUrl(private: true);

        return $path === null ? null : rtrim(config('hosts.admin_url'), '/').$path;
    }

    public static function table(Table $table): Table
    {
        return static::directoryTable($table->columns([
            ImageColumn::make('portrait')->label('Zdjęcie')->circular()->size(28)
                ->getStateUsing(self::portraitUrl(...)),
            TextColumn::make('name')->extraAttributes(['class' => 'panel-record-title'])->label('Imię i nazwisko')->searchable()->wrap(),
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
