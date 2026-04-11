<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;
use App\Models\Municipality;
use App\Models\Barangay;
use App\Models\FormSubmission;
use App\Models\FormDefinition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SystemStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        // Hide this from the default Dashboard discovery if it's meant for System Monitoring only
        // Wait, if it was already hidden? Let's check if it's displayed on Dashboard.
        return request()->routeIs('filament.admin.pages.system-monitoring');
    }

    protected function getStats(): array
    {
        $dbSize = 'N/A';
        try {
            if (DB::getDriverName() === 'sqlite') {
                $dbPath = database_path('database.sqlite');
                if (file_exists($dbPath)) {
                    $dbSize = round(filesize($dbPath) / 1024 / 1024, 2) . ' MB';
                }
            }
        } catch (\Exception $e) {
            $dbSize = 'N/A';
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $isAdmin = $user->hasRole(['super_admin', 'admin']);

        $stats = [
            Stat::make('Total Users', User::count())
                ->description('Registered accounts')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
            Stat::make('Total Municipalities', Municipality::count())
                ->description('Registered municipalities')
                ->color('success'),
            Stat::make('Total Barangays', Barangay::count())
                ->description('Registered barangays')
                ->color('warning'),
            Stat::make('Total Submissions', FormSubmission::count())
                ->description('Recorded data submissions')
                ->color('info'),
            Stat::make('Forms', FormDefinition::count())
                ->description('Available forms')
                ->color('secondary'),
        ];

        if ($isAdmin) {
            $stats[] = Stat::make('Database Size', $dbSize)
                ->description('Estimated storage usage')
                ->descriptionIcon('heroicon-m-circle-stack');
        }

        return $stats;
    }
}
