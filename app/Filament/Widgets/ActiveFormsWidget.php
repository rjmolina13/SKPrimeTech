<?php

namespace App\Filament\Widgets;

use App\Models\Barangay;
use App\Models\FormDefinition;
use App\Models\Municipality;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\Cache;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

class ActiveFormsWidget extends TableWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user && $user->hasRole(['super_admin', 'admin']);
    }

    public function table(Table $table): Table
    {
        $monthSortSql = "CASE 
            WHEN name LIKE '%January%' THEN 1
            WHEN name LIKE '%February%' THEN 2
            WHEN name LIKE '%March%' THEN 3
            WHEN name LIKE '%April%' THEN 4
            WHEN name LIKE '%May%' THEN 5
            WHEN name LIKE '%June%' THEN 6
            WHEN name LIKE '%July%' THEN 7
            WHEN name LIKE '%August%' THEN 8
            WHEN name LIKE '%September%' THEN 9
            WHEN name LIKE '%October%' THEN 10
            WHEN name LIKE '%November%' THEN 11
            WHEN name LIKE '%December%' THEN 12
            ELSE 13 
        END";

        return $table
            ->heading(new \Illuminate\Support\HtmlString('
                <div class="flex items-center gap-2">
                    <span>Active Forms</span>
                    <button
                        wire:click="$refresh"
                        type="button"
                        wire:loading.attr="disabled"
                        wire:target="$refresh"
                        class="group inline-flex items-center justify-center text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400 transition-colors focus:outline-none disabled:cursor-wait"
                        title="Refresh Table"
                        aria-label="Refresh Table"
                    >
                        <svg wire:loading.remove.delay.shortest wire:target="$refresh" class="w-5 h-5 transition-transform duration-300 group-hover:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <svg wire:loading.delay.shortest wire:target="$refresh" class="w-5 h-5 animate-spin text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path clip-rule="evenodd" d="M12 19C15.866 19 19 15.866 19 12C19 8.13401 15.866 5 12 5C8.13401 5 5 8.13401 5 12C5 15.866 8.13401 19 12 19ZM12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" fill="currentColor" fill-rule="evenodd" opacity="0.2" />
                            <path d="M2 12C2 6.47715 6.47715 2 12 2V5C8.13401 5 5 8.13401 5 12H2Z" fill="currentColor" />
                        </svg>
                    </button>
                </div>
            '))
            ->query(
                FormDefinition::query()
                    ->where('is_active', true)
                    ->with(['submissions' => function ($query) {
                        $query->select('id', 'form_definition_id', 'record_id', 'record_type')
                            ->where('status', '!=', 'draft');
                    }])
            )
            ->defaultSort(fn($query) => $query->orderByRaw($monthSortSql . " ASC")->orderBy('name', 'asc'))
            ->columns([
                TextColumn::make('name')
                    ->label('Form Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('deadline')
                    ->label('Deadline')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->placeholder('No deadline'),
                TextColumn::make('scope')
                    ->label('Scope')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'barangay' => 'primary',
                        'municipality' => 'success',
                        'both' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => ucfirst($state)),
                TextColumn::make('progress')
                    ->label('Submission Progress')
                    ->getStateUsing(function (FormDefinition $record) {
                        $submissionsCount = $record->submissions->unique(function ($sub) {
                            return $sub->record_type . '-' . $sub->record_id;
                        })->count();

                        $totalCount = match ($record->scope) {
                            'barangay' => Cache::rememberForever('total_barangays', fn() => Barangay::count()),
                            'municipality' => Cache::rememberForever('total_municipalities', fn() => Municipality::count()),
                            'both' => Cache::rememberForever('total_barangays', fn() => Barangay::count()) + Cache::rememberForever('total_municipalities', fn() => Municipality::count()),
                            default => 0,
                        };

                        $paddedCount = str_pad($submissionsCount, 2, '0', STR_PAD_LEFT);
                        return "{$paddedCount}/{$totalCount}";
                    })
                    ->badge()
                    ->color(function ($state) {
                        [$sub, $tot] = explode('/', $state);
                        if ($tot == 0) return 'gray';
                        $ratio = $sub / $tot;
                        if ($ratio == 1) return 'success';
                        if ($ratio >= 0.5) return 'warning';
                        return 'danger';
                    }),
            ])
            ->recordAction('view_progress')
            ->recordActions([
                Action::make('view_progress')
                    ->label('View Progress')
                    ->icon('heroicon-o-chart-pie')
                    ->modalHeading(fn(FormDefinition $record) => "Submission Progress: {$record->name}")
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn(FormDefinition $record) => view('filament.widgets.active-forms-progress', [
                        'record' => $record,
                        'submissions' => $record->submissions,
                    ])),
            ]);
    }
}
