<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\SystemStatsOverview;

class SystemMonitoring extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';
    
    protected static string|\UnitEnum|null $navigationGroup = 'System Administration';
    
    protected static ?string $navigationLabel = 'System Health';

    protected static ?string $title = 'System Health';

    protected string $view = 'filament.pages.system-monitoring';
    
    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        return $user && $user->hasRole('super_admin');
    }
    
    protected function getHeaderWidgets(): array
    {
        return [];
    }
}
