<?php

namespace App\Filament\Resources\FormSubmissions;

use App\Filament\Resources\FormSubmissions\Pages\CreateFormSubmission;
use App\Filament\Resources\FormSubmissions\Pages\EditFormSubmission;
use App\Filament\Resources\FormSubmissions\Pages\ListFormSubmissions;
use App\Filament\Resources\FormSubmissions\Schemas\FormSubmissionForm;
use App\Filament\Resources\FormSubmissions\Tables\FormSubmissionsTable;
use App\Models\FormSubmission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FormSubmissionResource extends Resource
{
    protected static ?string $model = FormSubmission::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Submitted Reports';

    protected static ?string $modelLabel = 'Submitted Report';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports & Compliance';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();

        if ($user && $user->hasRole('municipal') && $user->municipality_id) {
            // For municipal users, show submissions where:
            // 1. record_type is Municipality AND record_id is their municipality
            // 2. OR record_type is Barangay AND the barangay belongs to their municipality
            $query->where(function ($q) use ($user) {
                $q->where(function ($subQ) use ($user) {
                    $subQ->where('record_type', \App\Models\Municipality::class)
                        ->where('record_id', $user->municipality_id);
                })->orWhere(function ($subQ) use ($user) {
                    $subQ->where('record_type', \App\Models\Barangay::class)
                        ->whereHas('record', function ($barangayQ) use ($user) {
                            $barangayQ->where('municipality_id', $user->municipality_id);
                        });
                });
            });
        }

        return $query;
    }

    public static function getRecordRouteKeyName(): ?string
    {
        return 'guid';
    }

    public static function form(Schema $schema): Schema
    {
        return FormSubmissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FormSubmissionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFormSubmissions::route('/'),
            'create' => CreateFormSubmission::route('/create'),
            'edit' => EditFormSubmission::route('/{record}/edit'),
        ];
    }
}
