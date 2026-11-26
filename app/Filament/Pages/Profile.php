<?php

namespace App\Filament\Pages;

use App\Http\Middleware\RequireCurrentAccountSession;
use App\Models\User;
use Filament\Auth\Pages\EditProfile;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use SensitiveParameter;

final class Profile extends EditProfile
{
    protected static ?string $title = 'Mój profil';

    protected Width|string|null $maxContentWidth = Width::FourExtraLarge;

    public function defaultForm(Schema $schema): Schema
    {
        return parent::defaultForm($schema)->inlineLabel(false);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dane konta')->compact()->columns(2)->schema([
                $this->getNameFormComponent()->label('Imię i nazwisko')->autofocus(false),
                $this->getEmailFormComponent()->disabled()->dehydrated(false)->live(false),
            ]),
            Section::make('Zmiana hasła')->compact()->columns(2)->schema([
                $this->getCurrentPasswordFormComponent()->columnSpanFull(),
                $this->getPasswordFormComponent()->live(false),
                $this->getPasswordConfirmationFormComponent()->visible(true)
                    ->required(fn (Get $get): bool => filled($get('password'))),
            ]),
        ]);
    }

    protected function getCurrentPasswordFormComponent(): Component
    {
        return parent::getCurrentPasswordFormComponent()->label('Obecne hasło')
            ->belowContent(null)->visible(true)->required();
    }

    protected function mutateFormDataBeforeSave(#[SensitiveParameter] array $data): array
    {
        $this->data['currentPassword'] = null;

        return Arr::only($data, ['name', 'password']);
    }

    protected function handleRecordUpdate(Model $record, #[SensitiveParameter] array $data): Model
    {
        $changePassword = array_key_exists('password', $data);
        $updated = DB::transaction(function () use ($record, $data, $changePassword): User {
            $user = User::query()->lockForUpdate()->findOrFail($record->getKey());
            abort_unless($user->is_active
                && $user->session_version === request()->session()->get(RequireCurrentAccountSession::SESSION_KEY)
                && hash_equals($user->getAuthPassword(), $record->getAuthPassword()), 403);

            $user->fill($data);
            if ($changePassword) {
                $user->session_version++;
                Password::broker()->deleteToken($user);
            }
            $user->save();

            return $user;
        });

        if ($changePassword) {
            $record->setRawAttributes($updated->getAttributes(), sync: true);
            request()->session()->put(RequireCurrentAccountSession::SESSION_KEY, $updated->session_version);
            request()->session()->regenerate(true);
        }

        return $updated;
    }
}
