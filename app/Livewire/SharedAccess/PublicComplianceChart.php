<?php

namespace App\Livewire\SharedAccess;

use App\Filament\Widgets\ComplianceChart;

class PublicComplianceChart extends ComplianceChart
{
    public static function canView(): bool
    {
        return true;
    }
}
