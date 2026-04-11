<?php

namespace App\Console\Commands;

use App\Models\Barangay;
use App\Models\FormDefinition;
use App\Models\FormSubmission;
use App\Models\MonthlySystemStat;
use App\Models\Municipality;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class CaptureMonthlySystemStats extends Command
{
    protected $signature = 'stats:capture-monthly {--month=}';

    protected $description = 'Capture and persist end-of-month system statistics.';

    public function handle(): int
    {
        $monthStart = $this->resolveMonthStart();

        if (! $monthStart) {
            return self::FAILURE;
        }

        $monthEnd = $monthStart->endOfMonth()->endOfDay();

        $stats = [
            'total_users' => User::query()->whereDate('created_at', '<=', $monthEnd)->count(),
            'total_municipalities' => Municipality::query()->whereDate('created_at', '<=', $monthEnd)->count(),
            'total_barangays' => Barangay::query()->whereDate('created_at', '<=', $monthEnd)->count(),
            'total_forms' => FormDefinition::query()->whereDate('created_at', '<=', $monthEnd)->count(),
            'total_submissions' => FormSubmission::query()->whereDate('created_at', '<=', $monthEnd)->count(),
            'approved_submissions' => FormSubmission::query()->whereDate('created_at', '<=', $monthEnd)->where('status', 'approved')->count(),
            'pending_submissions' => FormSubmission::query()->whereDate('created_at', '<=', $monthEnd)->whereIn('status', ['submitted', 'under_review'])->count(),
            'rejected_submissions' => FormSubmission::query()->whereDate('created_at', '<=', $monthEnd)->where('status', 'rejected')->count(),
        ];

        $monthStartDate = $monthStart->toDateString();

        $monthlyStat = MonthlySystemStat::query()
            ->whereDate('month_start', $monthStartDate)
            ->first();

        if ($monthlyStat) {
            $monthlyStat->fill([
                'stats' => $stats,
                'captured_at' => now(),
            ]);
            $monthlyStat->save();

            $this->info('Monthly system stats already existed for '.$monthStart->format('F Y').'. Record refreshed.');

            return self::SUCCESS;
        }

        MonthlySystemStat::query()->create([
            'month_start' => $monthStartDate,
            'stats' => $stats,
            'captured_at' => now(),
        ]);

        $this->info('Monthly system stats captured for '.$monthStart->format('F Y').'.');

        return self::SUCCESS;
    }

    protected function resolveMonthStart(): ?CarbonImmutable
    {
        $month = $this->option('month');

        $currentMonthStart = CarbonImmutable::now()->startOfMonth();
        $defaultMonthStart = $currentMonthStart->subMonthNoOverflow()->startOfMonth();

        if (! $month) {
            return $defaultMonthStart;
        }

        try {
            $monthStart = CarbonImmutable::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Throwable) {
            $this->error('Invalid --month value. Use YYYY-MM format.');

            return null;
        }

        if ($monthStart->greaterThanOrEqualTo($currentMonthStart)) {
            $this->error('Cannot capture unfinished or future months. Use a month earlier than '.$currentMonthStart->format('Y-m').'.');

            return null;
        }

        return $monthStart;
    }
}
