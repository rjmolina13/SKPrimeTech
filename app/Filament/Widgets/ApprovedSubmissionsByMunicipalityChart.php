<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class ApprovedSubmissionsByMunicipalityChart extends ChartWidget
{
    protected ?string $heading = 'Approved Submissions by Municipality';
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 1;
    protected ?string $maxHeight = '350px';

    public static function canView(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        return $user?->hasRole(['super_admin', 'admin']) ?? false;
    }

    protected function getData(): array
    {
        // Count FormSubmissions by Municipality (Directly or via Barangay)
        // Filter by status = 'approved'
        
        // 1. Submissions linked directly to Municipality
        $municipalSubmissions = \App\Models\FormSubmission::query()
            ->where('status', 'approved')
            ->where('record_type', \App\Models\Municipality::class)
            ->with('record')
            ->get();
            
        $groupedMunicipal = $municipalSubmissions->groupBy(fn ($submission) => $submission->record->name ?? 'Unknown')
            ->map->count();

        // 2. Submissions linked to Barangays (need to traverse up to Municipality)
        $barangaySubmissions = \App\Models\FormSubmission::query()
            ->where('status', 'approved')
            ->where('record_type', \App\Models\Barangay::class)
            ->with('record.municipality')
            ->get();
            
        $groupedBarangay = $barangaySubmissions->groupBy(fn ($submission) => $submission->record->municipality->name ?? 'Unknown')
            ->map->count();
            
        // 3. Merge counts
        $merged = $groupedMunicipal->toArray();
        $barangayCounts = $groupedBarangay->toArray();
        
        foreach ($barangayCounts as $municipality => $count) {
            if (isset($merged[$municipality])) {
                $merged[$municipality] += $count;
            } else {
                $merged[$municipality] = $count;
            }
        }
        
        // Ensure we have some data to display even if empty
        if (empty($merged)) {
            $merged = ['No Data' => 0];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Approved Submissions',
                    'data' => array_values($merged),
                    'backgroundColor' => [
                        '#4BC0C0', '#36A2EB', '#FF6384', '#FF9F40', '#9966FF', '#FFCD56', '#C9CBCF'
                    ],
                ],
            ],
            'labels' => array_keys($merged),
        ];
    }

    protected function getOptions(): array|\Filament\Support\RawJs|null
    {
        return \Filament\Support\RawJs::make(<<<JS
        {
            aspectRatio: 1,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                },
            },
            onClick: function(event, elements) {
                if (elements.length > 0) {
                    var index = elements[0].index;
                    var label = this.data.labels[index];
                    if (label !== 'No Data') {
                        Livewire.dispatch('openMunicipalityRecordsModal', { name: label, chartTitle: 'Approved Submissions by Municipality' });
                        window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'municipality-records' } }));
                    }
                }
            }
        }
        JS);
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
