<?php

namespace App\Console\Commands;

use App\Models\FormDefinition;
use Illuminate\Console\Command;

class ReportsTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:templates {--limit=20 : The number of templates to display}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and output a list of report templates (Form Definitions) to the terminal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $this->info("Fetching the latest {$limit} report templates...");

        $templates = FormDefinition::latest()
            ->limit($limit)
            ->get(['id', 'guid', 'name', 'scope', 'is_active', 'frequency', 'created_at']);

        if ($templates->isEmpty()) {
            $this->warn('No report templates found.');
            return self::SUCCESS;
        }

        $headers = ['ID', 'GUID', 'Name', 'Scope', 'Status', 'Frequency', 'Created At'];
        
        $rows = $templates->map(function ($template) {
            return [
                $template->id,
                $template->guid ?? 'N/A',
                $template->name,
                ucfirst($template->scope),
                $template->is_active ? '<info>Active</info>' : '<error>Inactive</error>',
                ucfirst($template->frequency),
                $template->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        $this->table($headers, $rows);

        return self::SUCCESS;
    }
}
