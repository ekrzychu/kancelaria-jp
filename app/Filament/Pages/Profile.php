<?php

namespace App\Filament\Pages;

use App\Filament\Forms\ImageUpload;
use App\Http\Middleware\RequireCurrentAccountSession;
use App\Media\MediaPurpose;
use App\Media\MediaReferences;
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
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use SensitiveParameter;

final class Profile extends EditProfile
{
    protected static ?string $title = 'Mój profil';

    protected Width|string|null $maxContentWidth = Width::FourExtraLarge;

    #[Locked]
    public ?string $originalAvatar = null;

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
                ImageUpload::make('avatar_media_id')->label('Zdjęcie profilowe')->purpose(MediaPurpose::Avatar)
                    ->helperText('Zdjęcie jest widoczne w Twoim panelu administracyjnym.')->columnSpanFull(),
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

        return Arr::only($data, ['name', 'password', 'avatar_media_id']);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->originalAvatar = $data['avatar_media_id'] ?? null;

        return $data;
    }

    protected function afterSave(): void
    {
        $this->dispatch('refresh-topbar');
    }

    protected function handleRecordUpdate(Model $record, #[SensitiveParameter] array $data): Model
    {
        $changePassword = array_key_exists('password', $data);
        $updated = DB::transaction(function () use ($record, $data, $changePassword): User {
            $user = User::query()->lockForUpdate()->findOrFail($record->getKey());
            abort_unless($user->is_active
                && $user->session_version === request()->session()->get(RequireCurrentAccountSession::SESSION_KEY)
                && hash_equals($user->getAuthPassword(), $record->getAuthPassword()), 403);

            $avatar = $data['avatar_media_id'] ?? null;
            if ($avatar !== $this->originalAvatar) {
                if ($user->avatar_media_id !== $this->originalAvatar || ($avatar !== null && ! is_string($avatar))) {
                    throw ValidationException::withMessages(['data.avatar_media_id' => 'Nie można zmienić zdjęcia. Odśwież stronę i wybierz je ponownie.']);
                }
                if ($avatar !== null) {
                    app(MediaReferences::class)->bind($avatar, MediaPurpose::Avatar, $user, 'data.avatar_media_id');
                }
                $user->avatar_media_id = $avatar;
            }
            $user->fill(Arr::except($data, ['avatar_media_id']));
            if ($changePassword) {
                $user->session_version++;
                Password::broker()->deleteToken($user);
            }
            $user->save();

            return $user;
        });

        $record->setRawAttributes($updated->getAttributes(), sync: true);
        $this->originalAvatar = $this->data['avatar_media_id'] = $updated->avatar_media_id;
        if ($changePassword) {
            request()->session()->put(RequireCurrentAccountSession::SESSION_KEY, $updated->session_version);
            request()->session()->regenerate(true);
        }

        return $updated;
    }
}
