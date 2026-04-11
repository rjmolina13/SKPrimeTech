<?php

namespace App\Livewire\SharedAccess;

use App\Filament\Widgets\ApprovedSubmissionsByMunicipalityChart;

class PublicApprovedSubmissionsChart extends ApprovedSubmissionsByMunicipalityChart
{
    public static function canView(): bool
    {
        return true;
    }
}
