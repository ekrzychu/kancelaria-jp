<?php

namespace App\Console\Commands;

use App\Actions\ManageAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

final class ProvisionAdministrator extends Command
{
    protected $signature = 'panel:provision-administrator';

    protected $description = 'Create the first active administrator using an interactive, hidden password prompt';

    public function handle(ManageAccount $accounts): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('This command requires an interactive terminal.');

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Imię i nazwisko'));
        $email = strtolower(trim((string) $this->ask('Adres e-mail')));
        $password = $this->secret('Hasło (co najmniej 12 znaków)', false);
        $confirmation = $this->secret('Powtórz hasło', false);
        $validator = Validator::make(
            compact('name', 'email', 'password') + ['password_confirmation' => $confirmation],
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users',
                'password' => ['required', 'confirmed', Password::default()],
            ],
        );

        try {
            $validator->validate();
            $accounts->provisionFirstAdministrator($name, $email, $password);
        } catch (ValidationException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Utworzono konto administratora.');

        return self::SUCCESS;
    }
}
