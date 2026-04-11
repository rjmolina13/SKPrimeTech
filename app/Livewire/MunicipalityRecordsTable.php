<?php

namespace App\Livewire;

use App\Models\FormSubmission;
use App\Models\Municipality;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use App\Filament\Resources\FormSubmissions\Schemas\FormSubmissionForm;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Livewire\Attributes\On;

class MunicipalityRecordsTable extends Component implements HasTable, HasActions, HasSchemas
{
    use InteractsWithTable;
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ?string $municipalityName = null;
    public ?string $chartTitle = null;

    #[On('openMunicipalityRecordsModal')]
    public function setMunicipalityName($name, $chartTitle = null)
    {
        $this->municipalityName = $name;
        $this->chartTitle = $chartTitle;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                if (!$this->municipalityName || $this->municipalityName === 'No Data') {
                    return FormSubmission::query()->whereNull('id'); // Empty query
                }

                $municipality = Municipality::where('name', $this->municipalityName)->first();
                
                if (!$municipality) {
                    return FormSubmission::query()->whereNull('id');
                }

                // Submissions linked to the municipality directly or to its barangays
                return FormSubmission::query()
                    ->with(['formDefinition', 'record'])
                    ->where('status', 'approved')
                    ->where(function ($query) use ($municipality) {
                        $query->where(function ($q) use ($municipality) {
                            $q->where('record_type', \App\Models\Municipality::class)
                              ->where('record_id', $municipality->id);
                        })->orWhere(function ($q) use ($municipality) {
                            $q->where('record_type', \App\Models\Barangay::class)
                              ->whereIn('record_id', $municipality->barangays()->pluck('id'));
                        });
                    });
            })
            ->columns([
                Tables\Columns\TextColumn::make('formDefinition.name')
                    ->label('Form')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('record_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->badge()
                    ->color(fn (string $state): string => match (class_basename($state)) {
                        'Barangay' => 'warning',
                        'Municipality' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('record.name')
                    ->label('Record')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M j, Y h:i A')
                    ->label('Date')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->label('View Details')
                    ->iconButton()
                    ->tooltip('View Details')
                    ->modalAutofocus(false)
                    ->modalHeading(fn (Model $record) => $record->formDefinition->name ?? 'Submission Details')
                    ->schema(fn (Schema $schema) => FormSubmissionForm::configure($schema))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ]);
    }

    public function render()
    {
        return view('livewire.municipality-records-table');
    }
}
