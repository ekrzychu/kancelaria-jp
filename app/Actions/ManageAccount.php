<?php

namespace App\Actions;

use App\Enums\ContentSection;
use App\Models\User;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use SensitiveParameter;

final class ManageAccount
{
    public function provisionFirstAdministrator(string $name, string $email, #[SensitiveParameter] string $password): void
    {
        $this->withAccountLock(function () use ($name, $email, $password): void {
            if (User::where('is_admin', true)->where('is_active', true)->exists()) {
                throw ValidationException::withMessages(['email' => 'Istnieje już aktywny administrator.']);
            }

            (new User(compact('name', 'email', 'password')))->forceFill(['is_admin' => true])->save();
        });
    }

    public function create(#[SensitiveParameter] array $data): User
    {
        return $this->withAccountLock(function () use ($data): User {
            $this->authorize('create', User::class);
            $account = new User(Arr::only($data, ['name', 'email', 'password']));
            $account->forceFill($this->accessFields($data))->save();

            return $account;
        });
    }

    public function update(User $account, array $data): User
    {
        return $this->change($account, 'update', [
            ...Arr::only($data, ['name', 'email']),
            ...$this->accessFields($data),
        ]);
    }

    public function setActive(User $account, bool $active): void
    {
        $this->change($account, 'changeStatus', ['is_active' => $active]);
    }

    private function accessFields(array $data): array
    {
        $isAdmin = (string) ($data['is_admin'] ?? '0') === '1';

        return [
            'is_admin' => $isAdmin,
            'permissions' => $isAdmin ? [] : array_values(array_intersect(
                array_keys(ContentSection::options()),
                (array) ($data['permissions'] ?? []),
            )),
        ];
    }

    private function change(User $account, string $ability, array $fields): User
    {
        return $this->withAccountLock(function () use ($account, $ability, $fields): User {
            $current = User::query()->lockForUpdate()->findOrFail($account->getKey());
            $this->authorize($ability, $current);

            $originalEmail = $current->email;
            $wasAdministrator = $current->isActiveAdministrator();
            $current->forceFill($fields);
            if ($wasAdministrator && ! $current->isActiveAdministrator()
                && ! User::whereKeyNot($current->getKey())->where('is_admin', true)->where('is_active', true)->exists()) {
                throw ValidationException::withMessages([
                    'data.is_admin' => 'Musi pozostać co najmniej jeden aktywny administrator.',
                ]);
            }

            if ($current->isDirty(['is_active', 'email', 'is_admin', 'permissions'])) {
                $current->session_version++;
            }
            if ($current->isDirty(['is_active', 'email'])) {
                Password::broker()->deleteToken(new User(['email' => $originalEmail]));
            }
            $current->save();

            return $current;
        });
    }

    private function authorize(string $ability, User|string $subject): void
    {
        $actor = auth()->user()?->fresh();
        abort_unless($actor instanceof User, 403);
        Gate::forUser($actor)->authorize($ability, $subject);
    }

    private function withAccountLock(Closure $operation): mixed
    {
        return DB::transaction(function () use ($operation): mixed {
            // One stable InnoDB row serializes privilege changes even when the active admin set changes.
            DB::table('admin_account_lock')->where('id', 1)->lockForUpdate()->firstOrFail();

            return $operation();
        }, attempts: 3);
    }
}
