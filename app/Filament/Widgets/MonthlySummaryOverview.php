<?php

namespace App\Filament\Widgets;

use App\Models\Barangay;
use App\Models\FormDefinition;
use App\Models\FormSubmission;
use App\Models\MonthlySystemStat;
use App\Models\Municipality;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class MonthlySummaryOverview extends Widget
{
    protected string $view = 'filament.widgets.monthly-summary-overview';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $previousMonthStart = now()->subMonthNoOverflow()->startOfMonth();
        $sessionCache = request()->session()->cache();
        $previousMonthStats = $sessionCache->remember(
            $this->cacheKey('previous-month-stats', $previousMonthStart->format('Y-m')),
            now()->addMinutes(5),
            fn (): array => $this->buildSnapshot($previousMonthStart, now()->subMonthNoOverflow()->endOfMonth()->endOfDay())
        );

        $previousMonths = MonthlySystemStat::query()
            ->whereDate('month_start', '<', $previousMonthStart)
            ->orderByDesc('month_start')
            ->limit(12)
            ->get();

        return [
            'currentMonthLabel' => $previousMonthStart->format('F Y'),
            'currentMonthStats' => $previousMonthStats,
            'previousMonths' => $previousMonths,
            'statDefinitions' => $this->statDefinitions(),
        ];
    }

    protected function cacheKey(string $segment, string $month): string
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $sessionId = request()->session()->getId();
        $userKey = $user?->id ?? 'guest';

        return "dashboard:monthly-summary:{$segment}:{$month}:user:{$userKey}:session:{$sessionId}";
    }

    protected function buildSnapshot(Carbon $monthStart, Carbon $capturedAt): array
    {
        return [
            'total_users' => User::query()->whereDate('created_at', '<=', $capturedAt)->count(),
            'total_municipalities' => Municipality::query()->whereDate('created_at', '<=', $capturedAt)->count(),
            'total_barangays' => Barangay::query()->whereDate('created_at', '<=', $capturedAt)->count(),
            'total_forms' => FormDefinition::query()->whereDate('created_at', '<=', $capturedAt)->count(),
            'total_submissions' => FormSubmission::query()->whereDate('created_at', '<=', $capturedAt)->count(),
            'approved_submissions' => FormSubmission::query()->whereDate('created_at', '<=', $capturedAt)->where('status', 'approved')->count(),
            'pending_submissions' => FormSubmission::query()->whereDate('created_at', '<=', $capturedAt)->whereIn('status', ['submitted', 'under_review'])->count(),
            'rejected_submissions' => FormSubmission::query()->whereDate('created_at', '<=', $capturedAt)->where('status', 'rejected')->count(),
            'month_start' => $monthStart->toDateString(),
        ];
    }

    protected function statDefinitions(): array
    {
        return [
            'total_users' => [
                'label' => 'Total Users',
                'icon' => 'heroicon-m-user-group',
                'tone' => 'text-blue-700 dark:text-blue-400',
                'bg' => 'bg-blue-50 dark:bg-blue-500/10',
            ],
            'total_municipalities' => [
                'label' => 'Municipalities',
                'icon' => 'heroicon-m-map',
                'tone' => 'text-success-600 dark:text-success-400',
                'bg' => 'bg-success-50 dark:bg-success-500/10',
            ],
            'total_barangays' => [
                'label' => 'Barangays',
                'icon' => 'heroicon-m-building-office',
                'tone' => 'text-warning-600 dark:text-warning-400',
                'bg' => 'bg-warning-50 dark:bg-warning-500/10',
            ],
            'total_forms' => [
                'label' => 'Forms',
                'icon' => 'heroicon-m-document-duplicate',
                'tone' => 'text-gray-700 dark:text-gray-300',
                'bg' => 'bg-gray-100 dark:bg-gray-500/10',
            ],
            'total_submissions' => [
                'label' => 'Submissions',
                'icon' => 'heroicon-m-inbox-stack',
                'tone' => 'text-sky-700 dark:text-sky-400',
                'bg' => 'bg-sky-50 dark:bg-sky-500/10',
            ],
            'approved_submissions' => [
                'label' => 'Approved',
                'icon' => 'heroicon-m-check-badge',
                'tone' => 'text-success-600 dark:text-success-400',
                'bg' => 'bg-success-50 dark:bg-success-500/10',
            ],
            'pending_submissions' => [
                'label' => 'Pending',
                'icon' => 'heroicon-m-clock',
                'tone' => 'text-warning-600 dark:text-warning-400',
                'bg' => 'bg-warning-50 dark:bg-warning-500/10',
            ],
            'rejected_submissions' => [
                'label' => 'Rejected',
                'icon' => 'heroicon-m-x-circle',
                'tone' => 'text-red-700 dark:text-red-400',
                'bg' => 'bg-red-50 dark:bg-red-500/10',
            ],
        ];
    }
}
