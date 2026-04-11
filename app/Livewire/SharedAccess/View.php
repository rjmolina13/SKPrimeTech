<?php

namespace App\Livewire\SharedAccess;

use App\Models\SharedAccessLink;
use App\Filament\Resources\FormSubmissions\Schemas\FormSubmissionForm;
use App\Models\FormSubmission;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\ViewAction;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View as ViewContract;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public')]
class View extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public SharedAccessLink $link;

    public function mount(string $token): void
    {
        $this->link = SharedAccessLink::where('token', $token)->firstOrFail();

        if (!$this->link->isValid()) {
            abort(403, 'This link has expired or is inactive.');
        }

        $this->link->trackAccess();
    }

    public function table(Table $table): Table
    {
        $visibleRecordStatuses = $this->link->getVisibleRecordStatuses();

        return $table
            ->query(
                FormSubmission::query()
                    ->with(['formDefinition', 'record' => function ($morphTo) {
                        $morphTo->morphWith([\App\Models\Barangay::class => ['municipality']]);
                    }])
                    ->whereIn('status', $visibleRecordStatuses)
            )
            ->columns([
                TextColumn::make('formDefinition.name')
                    ->label('Report Type')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('record.name')
                    ->label('Record')
                    ->formatStateUsing(function ($state, FormSubmission $record) {
                        if ($record->record_type === \App\Models\Barangay::class && $record->record) {
                            $municipalityName = $record->record->municipality->name ?? '';
                            return $municipalityName ? "{$state} ({$municipalityName})" : $state;
                        }
                        return $state;
                    })
                    ->description(fn (FormSubmission $record) => $record->record_type === \App\Models\Barangay::class ? 'Barangay' : 'Municipality')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'warning',
                        'under_review' => 'info',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('Submitted On')
                    ->dateTime('M j, Y h:i A')
                    ->sortable()
                    ->wrap(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->label('View Details')
                    ->modalHeading(fn (FormSubmission $record) => $record->formDefinition->name ?? 'Submission Details')
                    ->schema(fn (Schema $schema) => FormSubmissionForm::configure($schema))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->recordAction('view')
            ->defaultSort('created_at', 'desc');
    }

    public function render(): ViewContract
    {
        return view('livewire.shared-access.view');
    }
}
