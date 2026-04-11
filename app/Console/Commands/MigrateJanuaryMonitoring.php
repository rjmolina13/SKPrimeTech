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
use RuntimeException;
use SplFileObject;

class MigrateJanuaryMonitoring extends Command
{
    protected $signature = 'reports:migrate-january-monitoring
        {--path=/Users/rjmolina13/Documents/trae_projects/SKPrime/.ignore/user_temp/temp_csv_jan_feb/jan : January CSV folder path}
        {--canonical-path=/Users/rjmolina13/Documents/trae_projects/SKPrime/.ignore/user_temp/temp_csv_jan_feb/feb : Canonical barangay roster path (February CSV folder)}
        {--source-template=February Monitoring : Source template to duplicate}
        {--target-template=January Monitoring : Target duplicated template}
        {--log= : Optional absolute log path}';

    protected $description = 'Duplicate February Monitoring template and inject January monitoring submissions with timestamp transformation and approval sequencing.';

    public function handle(): int
    {
        $sourceTemplateName = (string) $this->option('source-template');
        $targetTemplateName = (string) $this->option('target-template');
        $csvDirectory = (string) $this->option('path');
        $canonicalDirectory = (string) $this->option('canonical-path');

        if (! is_dir($csvDirectory)) {
            $this->error('CSV directory not found: '.$csvDirectory);

            return self::FAILURE;
        }

        if (! is_dir($canonicalDirectory)) {
            $this->error('Canonical CSV directory not found: '.$canonicalDirectory);

            return self::FAILURE;
        }

        $logPath = $this->resolveLogPath();
        $this->writeLog($logPath, 'Starting January monitoring migration.');
        $this->writeLog($logPath, 'CSV directory: '.$csvDirectory);
        $this->writeLog($logPath, 'Canonical roster directory: '.$canonicalDirectory);
        $this->writeLog($logPath, 'Source template: '.$sourceTemplateName);
        $this->writeLog($logPath, 'Target template: '.$targetTemplateName);

        $submissionBase = CarbonImmutable::create(2026, 2, 11, 8, 0, 0, config('app.timezone', 'Asia/Manila'));
        $approvalBase = CarbonImmutable::create(2026, 2, 12, 13, 0, 0, config('app.timezone', 'Asia/Manila'));
        $templateStamp = CarbonImmutable::create(2026, 1, 10, 8, 0, 0, config('app.timezone', 'Asia/Manila'));

        try {
            DB::transaction(function () use (
                $sourceTemplateName,
                $targetTemplateName,
                $csvDirectory,
                $canonicalDirectory,
                $logPath,
                $submissionBase,
                $approvalBase,
                $templateStamp
            ): void {
                $sourceTemplate = FormDefinition::query()
                    ->where('name', $sourceTemplateName)
                    ->first();

                if (! $sourceTemplate) {
                    throw new RuntimeException('Source template not found: '.$sourceTemplateName);
                }

                $templateSchema = $this->buildJanuarySchema($sourceTemplate, $csvDirectory);

                $targetTemplate = FormDefinition::query()->updateOrCreate(
                    ['name' => $targetTemplateName],
                    [
                        'schema' => $templateSchema,
                        'scope' => $sourceTemplate->scope,
                        'is_active' => true,
                        'frequency' => 'monthly',
                        'frequency_option' => '10',
                        'deadline' => null,
                    ]
                );

                FormDefinition::query()
                    ->whereKey($targetTemplate->id)
                    ->update([
                        'created_at' => $templateStamp->format('Y-m-d H:i:s'),
                        'updated_at' => $templateStamp->format('Y-m-d H:i:s'),
                    ]);

                $this->writeLog($logPath, 'Template duplicated/transformed. ID='.$targetTemplate->id);
                $this->validateTemplateSchema($templateSchema);
                $this->writeLog($logPath, 'Template schema validation passed.');

                $canonicalRoster = $this->loadCanonicalRoster($canonicalDirectory);
                $rows = $this->collectRows($csvDirectory, $canonicalRoster, $logPath);
                $this->writeLog($logPath, 'Collected valid CSV rows: '.$rows->count());

                FormSubmission::query()
                    ->where('form_definition_id', $targetTemplate->id)
                    ->where('record_type', Barangay::class)
                    ->delete();

                $this->writeLog($logPath, 'Deleted existing January Monitoring submissions for clean re-import.');

                $created = 0;
                $missing = 0;
                $barangayLookupCache = [];

                foreach ($rows->values() as $row) {
                    $municipality = $this->resolveMunicipality($row['municipality']);

                    if (! $municipality) {
                        $missing++;
                        $this->writeLog($logPath, 'Missing municipality: '.$row['municipality']);
                        continue;
                    }

                    $barangayLookupCache[$municipality->id] ??= $this->buildBarangayLookup((int) $municipality->id);
                    $barangay = $this->resolveBarangay($row['barangay'], $barangayLookupCache[$municipality->id]);

                    if (! $barangay) {
                        $missing++;
                        $this->writeLog($logPath, 'Missing barangay: '.$row['municipality'].' / '.$row['barangay']);
                        continue;
                    }

                    $submittedBy = User::query()
                        ->where('municipality_id', $municipality->id)
                        ->orderBy('id')
                        ->value('id') ?? 1;

                    $submissionAt = $this->submissionTimestampAt($submissionBase, $created);
                    $approvalAt = $this->approvalTimestampAt($approvalBase, $created);

                    $payload = $this->mapPayload($row);

                    $submission = FormSubmission::query()->create([
                        'guid' => $this->generateUniqueSubmissionGuid(),
                        'form_definition_id' => $targetTemplate->id,
                        'record_id' => $barangay->id,
                        'record_type' => Barangay::class,
                        'data' => $payload,
                        'submitted_by' => $submittedBy,
                        'status' => 'approved',
                        'remarks' => 'Auto-approved by January migration script',
                    ]);

                    FormSubmission::query()
                        ->whereKey($submission->id)
                        ->update([
                            'created_at' => $submissionAt->format('Y-m-d H:i:s'),
                            'updated_at' => $approvalAt->format('Y-m-d H:i:s'),
                        ]);

                    $this->writeLog(
                        $logPath,
                        sprintf(
                            'Imported #%d | %s / %s | submission=%s | approval=%s | status=approved',
                            $submission->id,
                            $municipality->name,
                            $barangay->name,
                            $submissionAt->format('Y-m-d h:i A'),
                            $approvalAt->format('Y-m-d h:i A'),
                        )
                    );

                    $created++;
                }

                $this->validateTimestamps($targetTemplate->id, $logPath, $submissionBase, $approvalBase);
                $this->writeLog($logPath, 'Timestamp integrity validation passed.');
                $this->writeLog($logPath, 'Created approved submissions: '.$created);
                $this->writeLog($logPath, 'Skipped missing references: '.$missing);
            });
        } catch (\Throwable $exception) {
            $this->writeLog($logPath, 'Migration failed: '.$exception->getMessage());
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('January monitoring migration completed.');
        $this->line('Log file: '.$logPath);

        return self::SUCCESS;
    }

    private function buildJanuarySchema(FormDefinition $sourceTemplate, string $csvDirectory): array
    {
        $sourceSchema = is_array($sourceTemplate->schema) ? $sourceTemplate->schema : [];
        $statusField = collect($sourceSchema)->first(fn (array $field): bool => ($field['type'] ?? null) === 'select');
        $linkField = collect($sourceSchema)->first(fn (array $field): bool => ($field['type'] ?? null) === 'links');
        $reasonField = collect($sourceSchema)->first(fn (array $field): bool => ($field['name'] ?? '') === 'a_reason');
        $countField = collect($sourceSchema)->first(fn (array $field): bool => ($field['name'] ?? '') === 'e_ppa_count');

        if (! is_array($statusField) || ! is_array($linkField) || ! is_array($reasonField) || ! is_array($countField)) {
            throw new RuntimeException('Source template schema does not match expected February Monitoring structure.');
        }

        $ppaLabels = $this->extractPpaLabels($csvDirectory);
        $schema = [];

        foreach ($ppaLabels as $index => $label) {
            $letter = chr(ord('a') + $index);
            $statusName = "jan_{$letter}_status";
            $linkName = "jan_{$letter}_link";
            $reasonName = "jan_{$letter}_reason";

            $parent = $statusField;
            $parent['name'] = $statusName;
            $parent['label'] = "{$letter}) {$label}";
            $parent['required'] = true;

            $reason = $reasonField;
            $reason['name'] = $reasonName;
            $reason['label'] = "{$letter}) Non-compliant reason";
            $reason['visible_if_field'] = $statusName;
            $reason['visible_if_value'] = 'non-compliant';
            $reason['required'] = true;
            $reason['is_sub_question'] = true;

            $link = $linkField;
            $link['name'] = $linkName;
            $link['label'] = "{$letter}) Compliant link";
            $link['visible_if_field'] = $statusName;
            $link['visible_if_value'] = 'compliant';
            $link['required'] = true;
            $link['is_sub_question'] = true;
            $link['min_links'] = 1;
            $link['max_links'] = 1;

            $schema[] = $parent;
            $schema[] = $reason;
            $schema[] = $link;
        }

        $count = $countField;
        $count['name'] = 'jan_other_ppa_count';
        $count['label'] = 'g) Number of other PPAs Conducted';
        $count['required'] = true;
        unset($count['visible_if_field'], $count['visible_if_value'], $count['is_sub_question']);
        $schema[] = $count;

        return $schema;
    }

    private function extractPpaLabels(string $csvDirectory): array
    {
        $files = glob(rtrim($csvDirectory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'*.csv');
        if (! is_array($files) || count($files) === 0) {
            throw new RuntimeException('No CSV files found in '.$csvDirectory);
        }

        sort($files);

        foreach ($files as $filePath) {
            $file = new SplFileObject($filePath, 'r');
            $file->setFlags(SplFileObject::READ_CSV);
            $header = $file->fgetcsv();

            if (! is_array($header) || count($header) < 14) {
                continue;
            }

            return [
                $this->sanitizeLabel((string) $header[2]),
                $this->sanitizeLabel((string) $header[4]),
                $this->sanitizeLabel((string) $header[6]),
                $this->sanitizeLabel((string) $header[8]),
                $this->sanitizeLabel((string) $header[10]),
                $this->sanitizeLabel((string) $header[12]),
            ];
        }

        throw new RuntimeException('Unable to extract PPA headers from January CSV files.');
    }

    private function sanitizeLabel(string $label): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', str_replace(["\r", "\n"], ' ', $label)) ?? $label);
        $clean = preg_replace('/^\d+\.\)\s*/', '', $clean) ?? $clean;
        $clean = str_ireplace('february', '', $clean);
        $clean = preg_replace('/\bJanuary\b/i', '', $clean) ?? $clean;
        $clean = trim(preg_replace('/\s+/', ' ', $clean) ?? $clean);

        return $clean;
    }

    private function collectRows(string $csvDirectory, array $canonicalRoster, string $logPath): Collection
    {
        $files = glob(rtrim($csvDirectory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'*.csv');
        if (! is_array($files) || count($files) === 0) {
            throw new RuntimeException('No CSV files found in '.$csvDirectory);
        }

        sort($files);
        $rows = collect();

        foreach ($files as $filePath) {
            $municipalityName = pathinfo($filePath, PATHINFO_FILENAME);
            $canonicalBarangays = $canonicalRoster[$municipalityName] ?? [];
            $municipalityRowIndex = 0;
            $file = new SplFileObject($filePath, 'r');
            $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
            $file->setCsvControl(',');
            $file->fgetcsv();

            foreach ($file as $row) {
                if (! is_array($row) || count($row) < 14) {
                    continue;
                }

                $barangay = trim((string) ($row[1] ?? ''));
                if ($barangay === '' || $this->normalizeKey($barangay) === 'barangay') {
                    continue;
                }

                $canonicalBarangay = $canonicalBarangays[$municipalityRowIndex] ?? $barangay;
                $municipalityRowIndex++;

                $rows->push([
                    'municipality' => $municipalityName,
                    'barangay' => $canonicalBarangay,
                    'ppa1_status' => $this->normalizeCompliance((string) ($row[2] ?? '')),
                    'ppa1_link' => trim((string) ($row[3] ?? '')),
                    'ppa2_status' => $this->normalizeCompliance((string) ($row[4] ?? '')),
                    'ppa2_link' => trim((string) ($row[5] ?? '')),
                    'ppa3_status' => $this->normalizeCompliance((string) ($row[6] ?? '')),
                    'ppa3_link' => trim((string) ($row[7] ?? '')),
                    'ppa4_status' => $this->normalizeCompliance((string) ($row[8] ?? '')),
                    'ppa4_link' => trim((string) ($row[9] ?? '')),
                    'ppa5_status' => $this->normalizeCompliance((string) ($row[10] ?? '')),
                    'ppa5_link' => trim((string) ($row[11] ?? '')),
                    'ppa6_status' => $this->normalizeCompliance((string) ($row[12] ?? '')),
                    'ppa6_link' => trim((string) ($row[13] ?? '')),
                    'other_ppa_count' => $this->normalizeZeroToTen((string) ($row[14] ?? '0')),
                ]);
            }

            $this->writeLog($logPath, 'Parsed file: '.$municipalityName.' rows='.$rows->where('municipality', $municipalityName)->count());
        }

        return $rows;
    }

    private function loadCanonicalRoster(string $canonicalDirectory): array
    {
        $files = glob(rtrim($canonicalDirectory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'*.csv');
        if (! is_array($files) || count($files) === 0) {
            throw new RuntimeException('No canonical CSV files found in '.$canonicalDirectory);
        }

        sort($files);

        $roster = [];

        foreach ($files as $filePath) {
            $municipalityName = pathinfo($filePath, PATHINFO_FILENAME);
            $file = new SplFileObject($filePath, 'r');
            $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
            $file->setCsvControl(',');
            $file->fgetcsv();

            $barangays = [];
            foreach ($file as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $name = trim((string) ($row[0] ?? ''));
                if ($name === '' || $this->normalizeKey($name) === 'barangay') {
                    continue;
                }

                $barangays[] = $name;
            }

            $roster[$municipalityName] = $barangays;
        }

        return $roster;
    }

    private function mapPayload(array $row): array
    {
        $payload = [];

        for ($index = 1; $index <= 6; $index++) {
            $letter = chr(ord('a') + ($index - 1));
            $statusKey = "ppa{$index}_status";
            $linkKey = "ppa{$index}_link";
            $status = $row[$statusKey] ?? 'non-compliant';
            $link = $this->normalizeUrl((string) ($row[$linkKey] ?? ''));

            if ($status === 'compliant' && $link === null) {
                $status = 'non-compliant';
            }

            $payload["jan_{$letter}_status"] = $status;
            $payload["jan_{$letter}_reason"] = $status === 'non-compliant' ? 'No Post' : null;
            $payload["jan_{$letter}_link"] = $status === 'compliant' && $link ? [['url' => $link]] : [];
        }

        $payload['jan_other_ppa_count'] = $row['other_ppa_count'] ?? '0';

        return $payload;
    }

    private function validateTemplateSchema(array $schema): void
    {
        $hasFacebookField = collect($schema)->contains(function (array $field): bool {
            $label = (string) ($field['label'] ?? '');

            return str_contains(mb_strtolower($label), 'facebook page / profile link of the barangay sk if any');
        });

        if ($hasFacebookField) {
            throw new RuntimeException('Template validation failed: Facebook page field still exists.');
        }

        for ($index = 0; $index < 6; $index++) {
            $letter = chr(ord('a') + $index);
            $status = collect($schema)->first(fn (array $field): bool => ($field['name'] ?? null) === "jan_{$letter}_status");
            $reason = collect($schema)->first(fn (array $field): bool => ($field['name'] ?? null) === "jan_{$letter}_reason");
            $link = collect($schema)->first(fn (array $field): bool => ($field['name'] ?? null) === "jan_{$letter}_link");

            if (! $status || ! $reason || ! $link) {
                throw new RuntimeException("Template validation failed: missing hierarchy for PPA {$letter}.");
            }
        }
    }

    private function validateTimestamps(int $templateId, string $logPath, CarbonImmutable $submissionBase, CarbonImmutable $approvalBase): void
    {
        $submissions = FormSubmission::query()
            ->where('form_definition_id', $templateId)
            ->where('record_type', Barangay::class)
            ->orderBy('id')
            ->get(['id', 'created_at', 'updated_at', 'status']);

        $previousSubmission = null;
        $previousApproval = null;

        foreach ($submissions as $item) {
            $createdAt = CarbonImmutable::parse((string) $item->created_at, config('app.timezone', 'Asia/Manila'));
            $updatedAt = CarbonImmutable::parse((string) $item->updated_at, config('app.timezone', 'Asia/Manila'));

            if ($item->status !== 'approved') {
                throw new RuntimeException('Timestamp validation failed: found non-approved submission ID '.$item->id);
            }

            if (! $createdAt->isSameDay($submissionBase)) {
                throw new RuntimeException('Timestamp validation failed: submission date mismatch on ID '.$item->id);
            }

            if (! $updatedAt->isSameDay($approvalBase)) {
                throw new RuntimeException('Timestamp validation failed: approval date mismatch on ID '.$item->id);
            }

            if ($previousSubmission && $createdAt->lt($previousSubmission)) {
                throw new RuntimeException('Timestamp validation failed: submission timestamps not monotonic.');
            }

            if ($previousApproval && $updatedAt->lt($previousApproval)) {
                throw new RuntimeException('Timestamp validation failed: approval timestamps not monotonic.');
            }

            $previousSubmission = $createdAt;
            $previousApproval = $updatedAt;
        }

        $this->writeLog($logPath, 'Validated submission count: '.$submissions->count());
    }

    private function submissionTimestampAt(CarbonImmutable $base, int $index): CarbonImmutable
    {
        $minutes = intdiv($index, 2) * 3 + ($index % 2 === 1 ? 1 : 0);

        return $base->addMinutes($minutes);
    }

    private function approvalTimestampAt(CarbonImmutable $base, int $index): CarbonImmutable
    {
        return $base->addMinutes($index);
    }

    private function normalizeCompliance(string $value): string
    {
        $normalized = Str::lower(trim($value));

        return str_starts_with($normalized, 'non') ? 'non-compliant' : 'compliant';
    }

    private function normalizeUrl(string $value): ?string
    {
        $url = trim($value);
        if ($url === '') {
            return null;
        }

        if (! str_contains($url, '://')) {
            $url = 'https://'.$url;
        }

        return $url;
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

    private function resolveLogPath(): string
    {
        $provided = $this->option('log');
        if (is_string($provided) && trim($provided) !== '') {
            return trim($provided);
        }

        $timestamp = now()->format('Ymd_His');

        return storage_path('logs/january_migration_'.$timestamp.'.log');
    }

    private function writeLog(string $logPath, string $message): void
    {
        $line = '['.now()->format('Y-m-d H:i:s')."] {$message}\n";
        file_put_contents($logPath, $line, FILE_APPEND);
    }

    private function generateUniqueSubmissionGuid(): string
    {
        for ($attempt = 0; $attempt < 15; $attempt++) {
            $guid = str_pad((string) random_int(1000, 999999), 6, '0', STR_PAD_LEFT);

            $exists = FormSubmission::query()
                ->where('guid', $guid)
                ->exists();

            if (! $exists) {
                return $guid;
            }
        }

        throw new RuntimeException('Unable to generate unique submission GUID after multiple attempts.');
    }
}
