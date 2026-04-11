<?php

namespace App\Console\Commands;

use App\Models\Barangay;
use App\Models\FormSubmission;
use App\Models\Municipality;
use Illuminate\Console\Command;

class ClearRecentSubmittedReports extends Command
{
    protected $signature = 'reports:clear-recent
        {count : Number of latest reports to clear}
        {--municipality= : Municipality ID, code, or name}
        {--barangay= : Barangay ID, code, or name}
        {--force : Skip confirmation prompt}';

    protected $description = 'Clear the last N submitted reports of a municipality or barangay.';

    public function handle(): int
    {
        $count = (int) $this->argument('count');

        if ($count <= 0) {
            $this->error('Count must be greater than zero.');

            return self::FAILURE;
        }

        $municipalityInput = $this->option('municipality');
        $barangayInput = $this->option('barangay');

        $hasMunicipality = is_string($municipalityInput) && $municipalityInput !== '';
        $hasBarangay = is_string($barangayInput) && $barangayInput !== '';

        if ($hasMunicipality === $hasBarangay) {
            $this->error('Provide exactly one target using --municipality or --barangay.');

            return self::FAILURE;
        }

        if ($hasMunicipality) {
            return $this->clearMunicipalityReports($municipalityInput, $count);
        }

        return $this->clearBarangayReports($barangayInput, $count);
    }

    private function clearMunicipalityReports(string $identifier, int $count): int
    {
        $municipality = $this->resolveMunicipality($identifier);

        if (! $municipality) {
            $this->error('Municipality not found.');

            return self::FAILURE;
        }

        $barangayIds = $municipality->barangays()->pluck('id');

        $query = FormSubmission::query()
            ->where(function ($builder) use ($municipality, $barangayIds): void {
                $builder
                    ->where(function ($inner) use ($municipality): void {
                        $inner->where('record_type', Municipality::class)
                            ->where('record_id', $municipality->id);
                    })
                    ->orWhere(function ($inner) use ($barangayIds): void {
                        $inner->where('record_type', Barangay::class)
                            ->whereIn('record_id', $barangayIds);
                    });
            });

        return $this->deleteLatestReports(
            query: $query,
            count: $count,
            label: 'municipality '.$municipality->name.' ('.$municipality->code.')',
        );
    }

    private function clearBarangayReports(string $identifier, int $count): int
    {
        $barangay = $this->resolveBarangay($identifier);

        if (! $barangay) {
            $this->error('Barangay not found.');

            return self::FAILURE;
        }

        $query = FormSubmission::query()
            ->where('record_type', Barangay::class)
            ->where('record_id', $barangay->id);

        return $this->deleteLatestReports(
            query: $query,
            count: $count,
            label: 'barangay '.$barangay->name.' ('.$barangay->code.')',
        );
    }

    private function deleteLatestReports($query, int $count, string $label): int
    {
        $targets = (clone $query)
            ->latest('created_at')
            ->latest('id')
            ->limit($count)
            ->get(['id', 'guid', 'created_at']);

        if ($targets->isEmpty()) {
            $this->warn('No submitted reports found for '.$label.'.');

            return self::SUCCESS;
        }

        $this->warn('About to delete '.$targets->count().' report(s) for '.$label.'.');
        $this->table(
            ['ID', 'GUID', 'Created At'],
            $targets->map(fn (FormSubmission $submission): array => [
                $submission->id,
                $submission->guid,
                (string) $submission->created_at,
            ])->all()
        );

        if (! $this->option('force') && ! $this->confirm('Continue deleting these reports?', false)) {
            $this->info('Operation cancelled.');

            return self::SUCCESS;
        }

        $deleted = FormSubmission::query()
            ->whereIn('id', $targets->pluck('id'))
            ->delete();

        $this->info('Deleted '.$deleted.' report(s).');

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

    private function resolveBarangay(string $value): ?Barangay
    {
        if (is_numeric($value)) {
            $barangay = Barangay::query()->find((int) $value);
            if ($barangay) {
                return $barangay;
            }
        }

        $barangay = Barangay::query()->where('code', $value)->first();
        if ($barangay) {
            return $barangay;
        }

        return Barangay::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
            ->first();
    }
}
