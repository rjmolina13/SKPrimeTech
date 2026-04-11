<?php

namespace App\Filament\Widgets;

use App\Models\FormSubmission;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Action;
use App\Filament\Resources\FormSubmissions\FormSubmissionResource;

class ActionRequiredWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->hasRole(['super_admin', 'admin']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                $query = FormSubmission::query();

                if ($user->hasRole(['super_admin', 'admin'])) {
                    // Admins see pending reviews
                    return $query->whereIn('status', ['submitted', 'under_review']);
                } elseif ($user->hasRole('municipal')) {
                    // Municipal users see rejected or drafts
                    return $query->where('submitted_by', $user->id)
                                 ->whereIn('status', ['rejected', 'draft']);
                }
                
                return $query->whereRaw('1 = 0'); // No results for others
            })
            ->columns([
                Tables\Columns\TextColumn::make('formDefinition.name')
                    ->label('Report')
                    ->searchable(),
                Tables\Columns\TextColumn::make('record.name')
                    ->label('Entity'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'rejected' => 'danger',
                        'draft' => 'gray',
                        'submitted' => 'info',
                        'under_review' => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M j, Y h:i A')
                    ->label('Date')
                    ->sortable(),
            ])
            ->actions([
                Action::make('view')
                    ->url(fn (FormSubmission $record): string => \App\Filament\Resources\FormSubmissions\FormSubmissionResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
