<?php

namespace App\Filament\Auth;

use App\Models\User;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Login extends \Filament\Auth\Pages\Login
{
    public function authenticate(): ?LoginResponse
    {
        try {
            return parent::authenticate();
        } catch (ValidationException $exception) {
            $data = $this->form->getState();
            $email = strtolower((string) ($data['email'] ?? ''));
            $password = (string) ($data['password'] ?? '');
            $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

            if ($user && (! Hash::check($password, (string) $user->password))) {
                throw ValidationException::withMessages([
                    'data.password' => 'Incorrect password.',
                ]);
            }

            throw $exception;
        }
    }
}
