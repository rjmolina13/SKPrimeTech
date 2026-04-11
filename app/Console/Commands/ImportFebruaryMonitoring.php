<?php

namespace App\Console\Commands;

use App\Models\Barangay;
use App\Models\FormDefinition;
use App\Models\FormSubmission;
use App\Models\Municipality;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SplFileObject;

class ImportFebruaryMonitoring extends Command
{
    protected $signature = 'reports:import-february-monitoring
        {--path= : Directory path containing municipality CSV files}
        {--template=February Monitoring : Target report template name}
        {--month= : Force submission timestamps to month end (YYYY-MM)}';

    protected $description = 'Import February municipality CSV data into the February Monitoring report template.';

    public function handle(): int
    {
        $directory = $this->option('path');
        $directory = is_string($directory) && $directory !== ''
            ? $directory
            : base_path('user_temp/temp_csv_jan_feb/feb');

        if (! is_dir($directory)) {
            $this->error('CSV directory not found: '.$directory);

            return self::FAILURE;
        }

        $submissionTimestamp = $this->resolveSubmissionTimestamp();
        if ($submissionTimestamp === false) {
            return self::FAILURE;
        }

        $templateName = (string) $this->option('template');
        $schema = $this->buildSchema();

        $definition = FormDefinition::query()->updateOrCreate(
            ['name' => $templateName],
            [
                'schema' => $schema,
                'scope' => 'barangay',
                'is_active' => true,
                'frequency' => 'monthly',
                'frequency_option' => '28',
            ]
        );

        $files = glob(rtrim($directory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'*.csv');
        if ($files === false || count($files) === 0) {
            $this->error('No CSV files found in: '.$directory);

            return self::FAILURE;
        }

        sort($files);

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $warnings = [];

        DB::transaction(function () use ($files, $definition, $submissionTimestamp, &$created, &$updated, &$skipped, &$warnings): void {
            foreach ($files as $filePath) {
                $municipalityName = pathinfo($filePath, PATHINFO_FILENAME);
                $municipality = $this->resolveMunicipality($municipalityName);

                if (! $municipality) {
                    $warnings[] = 'Municipality not found for file: '.$municipalityName;
                    continue;
                }

                $submittedBy = User::query()
                    ->where('municipality_id', $municipality->id)
                    ->orderBy('id')
                    ->value('id') ?? 1;

                $barangayMap = $this->buildBarangayLookup((int) $municipality->id);

                $file = new SplFileObject($filePath, 'r');
                $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
                $file->setCsvControl(',');
                $file->fgetcsv();

                foreach ($file as $row) {
                    if (! is_array($row)) {
                        continue;
                    }

                    $barangaySource = $this->cell($row, 0);
                    if ($barangaySource === '' || $this->normalizeKey($barangaySource) === 'barangay') {
                        continue;
                    }

                    $barangay = $this->resolveBarangay($barangaySource, $barangayMap);
                    if (! $barangay) {
                        $warnings[] = 'Barangay not found: '.$municipalityName.' / '.$barangaySource;
                        $skipped++;
                        continue;
                    }

                    $facebookPage = $this->normalizeOptionalUrl($this->cell($row, 1));
                    $aStatus = $this->normalizeCompliance($this->cell($row, 4));
                    [$aLink, $aReason] = $this->mapLinkAndReason($aStatus, $this->cell($row, 5));
                    $bStatus = $this->normalizeCompliance($this->cell($row, 6));
                    [$bLink, $bReason] = $this->mapLinkAndReason($bStatus, $this->cell($row, 7));
                    $cStatus = $this->normalizeCompliance($this->cell($row, 8));
                    [$cLink, $cReason] = $this->mapLinkAndReason($cStatus, $this->cell($row, 9));
                    $dStatus = $this->normalizeCompliance($this->cell($row, 10));
                    [$dLink, $dReason] = $this->mapLinkAndReason($dStatus, $this->cell($row, 11));
                    $eCount = $this->normalizeZeroToTen($this->cell($row, 12));

                    $payload = [
                        'facebook_page_link' => $facebookPage,
                        'a_status' => $aStatus,
                        'a_link' => $aLink,
                        'a_reason' => $aReason,
                        'b_status' => $bStatus,
                        'b_link' => $bLink,
                        'b_reason' => $bReason,
                        'c_status' => $cStatus,
                        'c_link' => $cLink,
                        'c_reason' => $cReason,
                        'd_status' => $dStatus,
                        'd_link' => $dLink,
                        'd_reason' => $dReason,
                        'e_ppa_count' => $eCount,
                    ];

                    $existing = FormSubmission::query()
                        ->where('form_definition_id', $definition->id)
                        ->where('record_type', Barangay::class)
                        ->where('record_id', $barangay->id)
                        ->latest('id')
                        ->first();

                    if ($existing) {
                        $status = $existing->status === 'approved' ? 'approved' : 'submitted';
                        $updateData = [
                            'data' => $payload,
                            'submitted_by' => $submittedBy,
                            'status' => $status,
                            'remarks' => null,
                        ];

                        $existing->update($updateData);

                        if ($submissionTimestamp instanceof CarbonImmutable) {
                            $timestamp = $submissionTimestamp->format('Y-m-d H:i:s');
                            FormSubmission::query()
                                ->whereKey($existing->id)
                                ->update([
                                    'created_at' => $timestamp,
                                    'updated_at' => $timestamp,
                                ]);
                        }

                        $updated++;
                        continue;
                    }

                    $createData = [
                        'form_definition_id' => $definition->id,
                        'record_id' => $barangay->id,
                        'record_type' => Barangay::class,
                        'data' => $payload,
                        'submitted_by' => $submittedBy,
                        'status' => 'submitted',
                        'remarks' => null,
                    ];

                    $submission = FormSubmission::query()->create($createData);

                    if ($submissionTimestamp instanceof CarbonImmutable) {
                        $timestamp = $submissionTimestamp->format('Y-m-d H:i:s');
                        FormSubmission::query()
                            ->whereKey($submission->id)
                            ->update([
                                'created_at' => $timestamp,
                                'updated_at' => $timestamp,
                            ]);
                    }

                    $created++;
                }
            }
        });

        $this->info('Import completed for template: '.$definition->name);
        $this->line('Created: '.$created);
        $this->line('Updated: '.$updated);
        $this->line('Skipped: '.$skipped);

        foreach ($warnings as $warning) {
            $this->warn($warning);
        }

        return self::SUCCESS;
    }

    private function resolveSubmissionTimestamp(): CarbonImmutable|false|null
    {
        $month = $this->option('month');
        if (! is_string($month) || trim($month) === '') {
            return null;
        }

        try {
            $monthStart = CarbonImmutable::createFromFormat('Y-m', trim($month))->startOfMonth();
        } catch (\Throwable) {
            $this->error('Invalid --month value. Use YYYY-MM format.');

            return false;
        }

        if ($monthStart->greaterThan(CarbonImmutable::now()->endOfMonth())) {
            $this->error('Invalid --month value. Future months are not allowed.');

            return false;
        }

        return $monthStart->endOfMonth()->endOfDay();
    }

    private function cell(array $row, int $index): string
    {
        return trim((string) ($row[$index] ?? ''));
    }

    private function resolveMunicipality(string $sourceName): ?Municipality
    {
        $sourceKey = $this->normalizeKey($sourceName);

        $municipalities = Municipality::query()
            ->get(['id', 'name']);

        foreach ($municipalities as $municipality) {
            $name = (string) $municipality->name;
            $plainName = trim((string) preg_replace('/\s*\(.*\)\s*/', ' ', $name));

            if ($this->normalizeKey($name) === $sourceKey || $this->normalizeKey($plainName) === $sourceKey) {
                return $municipality;
            }
        }

        return null;
    }

    private function buildBarangayLookup(int $municipalityId): Collection
    {
        $lookup = collect();

        $barangays = Barangay::query()
            ->where('municipality_id', $municipalityId)
            ->get(['id', 'name']);

        foreach ($barangays as $barangay) {
            $name = (string) $barangay->name;
            $plainName = trim((string) preg_replace('/\s*\(.*\)\s*/', ' ', $name));

            $lookup->put($this->normalizeKey($name), $barangay);
            $lookup->put($this->normalizeKey($plainName), $barangay);
        }

        return $lookup;
    }

    private function resolveBarangay(string $sourceName, Collection $barangayMap): ?Barangay
    {
        $sourceKey = $this->normalizeKey($sourceName);
        $exact = $barangayMap->get($sourceKey);
        if ($exact instanceof Barangay) {
            return $exact;
        }

        if (preg_match('/district([123])/', $sourceKey, $districtMatch) === 1) {
            $districtToken = 'district'.$districtMatch[1];
            foreach ($barangayMap as $key => $barangay) {
                if (is_string($key) && $barangay instanceof Barangay && str_contains($key, $districtToken)) {
                    return $barangay;
                }
            }
        }

        $bestDistance = PHP_INT_MAX;
        $bestMatch = null;

        foreach ($barangayMap as $key => $barangay) {
            if (! is_string($key) || ! $barangay instanceof Barangay) {
                continue;
            }

            $distance = levenshtein($sourceKey, $key);
            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestMatch = $barangay;
            }
        }

        if ($bestMatch instanceof Barangay && $bestDistance <= 3) {
            return $bestMatch;
        }

        return null;
    }

    private function normalizeKey(string $value): string
    {
        $ascii = Str::ascii($value);
        $ascii = mb_strtolower($ascii);

        $ascii = preg_replace('/\bsta\b\.?/u', 'santa', $ascii) ?? $ascii;
        $ascii = preg_replace('/\bsto\b\.?/u', 'santo', $ascii) ?? $ascii;
        $ascii = preg_replace('/\bdistrict\s*i\b/u', 'district1', $ascii) ?? $ascii;
        $ascii = preg_replace('/\bpoblacion\b/u', '', $ascii) ?? $ascii;
        $ascii = preg_replace('/\bpob\b\.?/u', '', $ascii) ?? $ascii;
        $ascii = preg_replace('/\bp\.\b/u', '', $ascii) ?? $ascii;

        return preg_replace('/[^a-z0-9]+/u', '', $ascii) ?? '';
    }

    private function normalizeCompliance(string $value): string
    {
        $normalized = Str::of($value)->lower()->trim()->value();

        return str_starts_with($normalized, 'non') ? 'non-compliant' : 'compliant';
    }

    private function normalizeOptionalUrl(string $value): ?string
    {
        $normalized = trim($value);
        if ($normalized === '' || mb_strtolower($normalized) === 'n/a') {
            return null;
        }

        if (! str_contains($normalized, '://')) {
            return 'https://'.$normalized;
        }

        return $normalized;
    }

    private function mapLinkAndReason(string $status, string $notes): array
    {
        if ($status === 'non-compliant') {
            return [[], $notes !== '' ? $notes : 'No reason provided in source CSV.'];
        }

        $url = $this->normalizeOptionalUrl($notes);
        if ($url === null) {
            return [[], null];
        }

        return [[['url' => $url]], null];
    }

    private function normalizeZeroToTen(string $value): string
    {
        if (! is_numeric($value)) {
            return '0';
        }

        $number = (int) $value;
        $number = max(0, min(10, $number));

        return (string) $number;
    }

    private function buildSchema(): array
    {
        $complianceOptions = [
            ['label' => 'Compliant', 'value' => 'compliant'],
            ['label' => 'Non-compliant', 'value' => 'non-compliant'],
        ];

        $numericOptions = collect(range(0, 10))
            ->map(fn (int $value): array => ['label' => (string) $value, 'value' => (string) $value])
            ->values()
            ->all();

        return [
            [
                'label' => 'Facebook Page / Profile Link of the Barangay SK if any',
                'type' => 'text',
                'name' => 'facebook_page_link',
                'required' => false,
                'is_url' => true,
            ],
            [
                'label' => 'a) Convened scheduled regular meeting.',
                'type' => 'select',
                'name' => 'a_status',
                'options' => $complianceOptions,
                'required' => true,
            ],
            [
                'label' => 'a) Link/Notes',
                'type' => 'links',
                'name' => 'a_link',
                'required' => true,
                'visible_if_field' => 'a_status',
                'visible_if_value' => 'compliant',
                'is_sub_question' => true,
                'min_links' => 1,
                'max_links' => 1,
            ],
            [
                'label' => 'a) Non-compliance reason',
                'type' => 'text',
                'name' => 'a_reason',
                'required' => true,
                'visible_if_field' => 'a_status',
                'visible_if_value' => 'non-compliant',
                'is_sub_question' => true,
            ],
            [
                'label' => 'b) Barangay Road Clearing Operation (BaRCO) (DILG M.C. 2024-053)',
                'type' => 'select',
                'name' => 'b_status',
                'options' => $complianceOptions,
                'required' => true,
            ],
            [
                'label' => 'b) Link/Notes',
                'type' => 'links',
                'name' => 'b_link',
                'required' => true,
                'visible_if_field' => 'b_status',
                'visible_if_value' => 'compliant',
                'is_sub_question' => true,
                'min_links' => 1,
                'max_links' => 1,
            ],
            [
                'label' => 'b) Non-compliance reason',
                'type' => 'text',
                'name' => 'b_reason',
                'required' => true,
                'visible_if_field' => 'b_status',
                'visible_if_value' => 'non-compliant',
                'is_sub_question' => true,
            ],
            [
                'label' => 'c) Joined the Barangay Kalinisan Day (BarKaDa) (DILG M.C. 2024-059)',
                'type' => 'select',
                'name' => 'c_status',
                'options' => $complianceOptions,
                'required' => true,
            ],
            [
                'label' => 'c) Link/Notes',
                'type' => 'links',
                'name' => 'c_link',
                'required' => true,
                'visible_if_field' => 'c_status',
                'visible_if_value' => 'compliant',
                'is_sub_question' => true,
                'min_links' => 1,
                'max_links' => 1,
            ],
            [
                'label' => 'c) Non-compliance reason',
                'type' => 'text',
                'name' => 'c_reason',
                'required' => true,
                'visible_if_field' => 'c_status',
                'visible_if_value' => 'non-compliant',
                'is_sub_question' => true,
            ],
            [
                'label' => 'd) SK Web Portal Account (Section 8(c) of the SK Reform Act)',
                'type' => 'select',
                'name' => 'd_status',
                'options' => $complianceOptions,
                'required' => true,
            ],
            [
                'label' => 'd) Link/Notes',
                'type' => 'links',
                'name' => 'd_link',
                'required' => true,
                'visible_if_field' => 'd_status',
                'visible_if_value' => 'compliant',
                'is_sub_question' => true,
                'min_links' => 1,
                'max_links' => 1,
            ],
            [
                'label' => 'd) Non-compliance reason',
                'type' => 'text',
                'name' => 'd_reason',
                'required' => true,
                'visible_if_field' => 'd_status',
                'visible_if_value' => 'non-compliant',
                'is_sub_question' => true,
            ],
            [
                'label' => 'e) Number of other PPAs Conducted',
                'type' => 'select',
                'name' => 'e_ppa_count',
                'options' => $numericOptions,
                'required' => true,
            ],
        ];
    }
}
