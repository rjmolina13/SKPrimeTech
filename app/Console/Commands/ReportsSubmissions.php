<?php

namespace App\Console\Commands;

use App\Models\FormSubmission;
use Illuminate\Console\Command;

class ReportsSubmissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:submissions {--limit=20 : The number of submissions to display}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and output a list of submitted reports (Form Submissions) to the terminal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $this->info("Fetching the latest {$limit} submitted reports...");

        $submissions = FormSubmission::with(['formDefinition', 'record', 'submitter'])
            ->latest()
            ->limit($limit)
            ->get();

        if ($submissions->isEmpty()) {
            $this->warn('No submitted reports found.');
            return self::SUCCESS;
        }

        $headers = ['ID', 'GUID', 'Template Name', 'Record Type', 'Record Name', 'Status', 'Submitted By', 'Submitted At'];
        
        $rows = $submissions->map(function ($submission) {
            $statusColor = match ($submission->status) {
                'approved' => 'info',
                'rejected' => 'error',
                'submitted', 'under_review' => 'comment',
                default => 'question',
            };

            $recordType = class_basename($submission->record_type);
            $recordName = $submission->record->name ?? 'N/A';
            
            // Format Barangay name with Municipality
            if ($submission->record_type === \App\Models\Barangay::class && $submission->record) {
                $municipalityName = $submission->record->municipality->name ?? '';
                if ($municipalityName) {
                    $recordName .= " ({$municipalityName})";
                }
            }

            return [
                $submission->id,
                $submission->guid ?? 'N/A',
                $submission->formDefinition->name ?? 'Unknown',
                $recordType,
                $recordName,
                "<$statusColor>" . ucfirst(str_replace('_', ' ', $submission->status)) . "</$statusColor>",
                $submission->submitter->name ?? 'Unknown',
                $submission->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        $this->table($headers, $rows);

        return self::SUCCESS;
    }
}
