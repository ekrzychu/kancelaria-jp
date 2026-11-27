<?php

namespace App\Filament\Resources;

use App\Actions\ManageAccount;
use App\Enums\ContentSection;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\GridDirection;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'uzytkownicy';

    protected static ?string $navigationLabel = 'Użytkownicy';

    protected static ?string $modelLabel = 'użytkownik';

    protected static ?string $pluralModelLabel = 'użytkownicy';

    protected static string | UnitEnum | null $navigationGroup = 'Administracja';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dane konta')->compact()->columns(2)->columnSpanFull()->schema([
                TextInput::make('name')->label('Imię i nazwisko')->required()->maxLength(255),
                TextInput::make('email')->label('Adres e-mail')->email()->required()->maxLength(255)
                    ->trim()->unique(ignoreRecord: true),
                TextInput::make('password')->label('Hasło początkowe')->password()
                    ->autocomplete('new-password')->required()->rule(Password::default())->visibleOn('create'),
                TextInput::make('password_confirmation')->label('Potwierdź hasło')->password()
                    ->autocomplete('new-password')->required()->same('password')->dehydrated(false)->visibleOn('create'),
            ]),
            Section::make('Dostęp')->compact()->columnSpanFull()->schema([
                Select::make('is_admin')->label('Typ konta')
                    ->options(['0' => 'Pracownik', '1' => 'Administrator'])
                    ->default('0')->required()->selectablePlaceholder(false)->live()
                    ->formatStateUsing(fn ($state): string => (string) (int) $state),
                CheckboxList::make('permissions')->label('Uprawnienia')
                    ->options(ContentSection::options())->columns(['sm' => 2, 'lg' => 3])
                    ->gridDirection(GridDirection::Row)->default([])
                    ->visible(fn (Get $get): bool => ! (bool) $get('is_admin')),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->extraAttributes(['class' => 'panel-record-title'])->label('Imię i nazwisko')->searchable()->wrap()->weight('medium'),
                TextColumn::make('email')->label('Adres e-mail')->searchable()->wrap(),
                TextColumn::make('is_admin')->label('Typ konta')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Administrator' : 'Pracownik'),
                TextColumn::make('is_active')->label('Status konta')->extraAttributes(['class' => 'panel-table-status'])
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktywne' : 'Nieaktywne')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                TextColumn::make('last_login_at')->label('Ostatnie logowanie')->dateTime('d.m.Y, H:i', timezone: 'Europe/Warsaw')
                    ->placeholder('—')->sortable(),
            ])
            ->stackedOnMobile()
            ->defaultSort('name')
            ->recordActions([
                EditAction::make()->label('Edytuj')->icon(null),
                ActionGroup::make([self::statusAction(false), self::statusAction(true)])
                    ->label('Więcej działań')->icon(Heroicon::EllipsisHorizontal)->color('gray'),
            ]);
    }

    private static function statusAction(bool $active): Action
    {
        $label = $active ? 'Aktywuj ponownie' : 'Dezaktywuj';

        return Action::make($active ? 'reactivate' : 'deactivate')
            ->label($label)
            ->color($active ? 'gray' : 'danger')
            ->visible(fn (User $record): bool => $record->is_active !== $active)
            ->authorize('changeStatus')
            ->requiresConfirmation()
            ->modalHeading($active ? 'Aktywować ponownie konto?' : 'Dezaktywować konto?')
            ->modalSubmitActionLabel($label)
            ->modalDescription($active ? 'Użytkownik będzie mógł się zalogować.' : 'Dostęp zostanie zablokowany, a bieżące sesje wygasną.')
            ->action(function (User $record, Action $action) use ($active): void {
                try {
                    app(ManageAccount::class)->setActive($record, $active);
                } catch (ValidationException $exception) {
                    Notification::make()->warning()->title($exception->getMessage())->send();
                    $action->halt();
                }
            })
            ->successNotificationTitle($active ? 'Konto aktywne' : 'Konto dezaktywowane');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/dodaj'),
            'edit' => Pages\EditUser::route('/{record}/edytuj'),
        ];
    }
}
