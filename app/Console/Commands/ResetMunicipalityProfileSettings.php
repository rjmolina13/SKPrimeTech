<?php

namespace App\Console\Commands;

use App\Models\Municipality;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetMunicipalityProfileSettings extends Command
{
    protected $signature = 'municipality:reset-profile
        {municipality : Municipality ID, code, or name}
        {--email= : Specific municipal account email}
        {--force : Skip confirmation prompt}';

    protected $description = 'Reset municipality account profile customization to defaults.';

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

        $this->warn('This will reset profile customization for '.$user->email.'.');

        if (! $this->option('force') && ! $this->confirm('Continue resetting profile customization?', false)) {
            $this->info('Operation cancelled.');

            return self::SUCCESS;
        }

        $resetMunicipalityLogo = Schema::hasColumn('municipalities', 'logo_path');

        DB::transaction(function () use ($user, $municipality, $resetMunicipalityLogo): void {
            $user->forceFill([
                'avatar_type' => 'upload',
                'avatar_url' => null,
                'avatar_background' => null,
            ])->save();

            if ($resetMunicipalityLogo) {
                $municipality->forceFill(['logo_path' => null])->save();
            }
        });

        $this->info('Municipality profile customization reset to defaults.');
        $this->line('Account: '.$user->email);
        $this->line('Avatar defaults: avatar_type=upload, avatar_url=null, avatar_background=null');
        $this->line('Municipality logo reset: '.($resetMunicipalityLogo ? 'yes' : 'skipped (logo_path column missing)'));

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
