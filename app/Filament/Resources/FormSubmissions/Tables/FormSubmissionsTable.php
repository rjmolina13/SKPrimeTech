<?php

namespace App\Filament\Resources\FormSubmissions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Filament\Exports\FormSubmissionExporter;
use Filament\Actions\ExportAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\FormSubmissions\Schemas\FormSubmissionForm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class FormSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $isMunicipal = $user && $user->hasRole('municipal');

        return $table
            ->recordUrl(null)
            ->columns([
                TextColumn::make('formDefinition.name')
                    ->label('Form')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('record_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->badge()
                    ->color(fn (string $state): string => match (class_basename($state)) {
                        'Barangay' => 'warning',
                        'Municipality' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('record.name') // Assuming Barangay/Municipality have a 'name' attribute
                    ->label('Record')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('submitter.name')
                    ->label('Submitted By')
                    ->searchable()
                    ->sortable()
                    ->hidden($isMunicipal), // Hide for municipal users
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'under_review' => 'warning',
                        'submitted' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->html()
                    ->formatStateUsing(
                        fn (Model $record): HtmlString => new HtmlString(
                            '<div class="text-xs leading-tight whitespace-nowrap">' .
                            e($record->created_at?->format('m/d/Y')) .
                            '<br>' .
                            e($record->created_at?->format('h:i a')) .
                            '</div>'
                        ),
                    )
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'under_review' => 'Under Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                SelectFilter::make('form_definition_id')
                    ->relationship('formDefinition', 'name')
                    ->label('Form'),
                SelectFilter::make('submitted_by_name')
                    ->label('Submitted By')
                    ->options(fn (): array => \App\Models\FormSubmission::query()
                        ->join('users', 'form_submissions.submitted_by', '=', 'users.id')
                        ->whereNotNull('users.name')
                        ->where('users.name', '!=', '')
                        ->orderBy('users.name')
                        ->distinct()
                        ->pluck('users.name', 'users.name')
                        ->all())
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        $submittedByName = $data['value'] ?? null;

                        if (blank($submittedByName)) {
                            return $query;
                        }

                        return $query->whereHas('submitter', fn (Builder $submitterQuery): Builder => $submitterQuery->where('name', $submittedByName));
                    })
                    ->hidden($isMunicipal),
                SelectFilter::make('record_type')
                    ->label('Type')
                    ->options([
                        \App\Models\Barangay::class => 'Barangay',
                        \App\Models\Municipality::class => 'Municipality',
                    ]),
            ])
            ->recordActions([
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
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit')
                    ->hidden($isMunicipal),
                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Delete')
                    ->hidden($isMunicipal),
            ])
            ->recordAction('view')
            ->headerActions([
                ExportAction::make()
                    ->exporter(FormSubmissionExporter::class),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->hidden($isMunicipal),
                ]),
            ]);
    }
}
