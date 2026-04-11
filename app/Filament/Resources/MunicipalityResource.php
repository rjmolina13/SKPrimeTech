<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MunicipalityResource\Pages;
use App\Filament\Resources\MunicipalityResource\RelationManagers\BarangaysRelationManager;
use App\Filament\Resources\MunicipalityResource\RelationManagers\SkmfOfficialsRelationManager;
use App\Models\Municipality;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;

class MunicipalityResource extends Resource
{
    protected static ?string $model = Municipality::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Municipalities (SKMF)';

    protected static ?string $modelLabel = 'Municipality';

    protected static ?string $pluralModelLabel = 'Municipalities (SKMF)';

    protected static string|\UnitEnum|null $navigationGroup = 'Local Councils';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('code')->label('Zip Code')->required()->maxLength(50)->disabled(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SkmfOfficialsRelationManager::class,
            BarangaysRelationManager::class,
        ];
    }

    public static function getRecordRouteKeyName(): ?string
    {
        return 'code';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user) {
            return $query;
        }
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            return $query;
        }
        if ($user->hasRole('municipal') && $user->municipality_id) {
            return $query->where('id', $user->municipality_id);
        }
        // Allow other admins to see all municipalities if they are not specifically municipal scoped
        return $query;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(
                fn (\Illuminate\Database\Eloquent\Model $record): string => Pages\EditMunicipality::getUrl([$record->code]),
            )
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('code')->label('Zip Code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('barangays_count')->counts('barangays')->label('Barangays'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMunicipalities::route('/'),
            'create' => Pages\CreateMunicipality::route('/create'),
            'edit' => Pages\EditMunicipality::route('/{record}/edit'),
        ];
    }
}
