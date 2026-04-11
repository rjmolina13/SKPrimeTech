<?php

namespace App\Console\Commands;

use App\Models\Municipality;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetMunicipalityPassword extends Command
{
    protected $signature = 'municipality:reset-password
        {municipality : Municipality ID, code, or name}
        {--email= : Specific municipal account email}
        {--length=12 : Generated password length}';

    protected $description = 'Reset the password of a municipality account and print the newly generated password.';

    public function handle(): int
    {
        $municipality = $this->resolveMunicipality($this->argument('municipality'));

        if (! $municipality) {
            $this->error('Municipality not found.');

            return self::FAILURE;
        }

        $user = $this->resolveMunicipalUser($municipality);

        if (! $user) {
            return self::FAILURE;
        }

        $length = (int) $this->option('length');
        $length = max(8, min(64, $length));
        $newPassword = Str::password($length);

        DB::transaction(function () use ($user, $newPassword): void {
            $user->forceFill([
                'password' => Hash::make($newPassword),
                'remember_token' => null,
            ])->save();
        });

        $this->info('Password reset successful.');
        $this->line('Municipality: '.$municipality->name.' ('.$municipality->code.')');
        $this->line('Account: '.$user->email);
        $this->line('New Password: '.$newPassword);

        return self::SUCCESS;
    }

    private function resolveMunicipality(string $value): ?Municipality
    {
        if (is_numeric($value)) {
            $municipality = Municipality::query()->find((int) $value);
            if ($municipality) {
                return $municipality;
            }
        }

        $municipality = Municipality::query()->where('code', $value)->first();
        if ($municipality) {
            return $municipality;
        }

        return Municipality::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
            ->first();
    }

    private function resolveMunicipalUser(Municipality $municipality): ?User
    {
        $query = User::query()
            ->where('municipality_id', $municipality->id)
            ->role('municipal')
            ->orderBy('id');

        $email = $this->option('email');

        if (is_string($email) && $email !== '') {
            $query->where('email', $email);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->error('No municipal account found for the selected municipality.');

            return null;
        }

        if ($users->count() > 1 && (! is_string($email) || $email === '')) {
            $this->warn('Multiple municipal accounts found. Using the first account by ID.');
            foreach ($users as $candidate) {
                $this->line('- '.$candidate->id.' | '.$candidate->email);
            }
        }

        return $users->first();
    }
}
