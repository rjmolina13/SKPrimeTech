<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangayResource\Pages;
use App\Filament\Resources\BarangayResource\RelationManagers\DocumentSubmissionsRelationManager;
use App\Filament\Resources\BarangayResource\RelationManagers\SkOfficialsRelationManager;
use App\Models\Barangay;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

use Filament\Tables\Grouping\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group as FormGroup;

class BarangayResource extends Resource
{
    protected static ?string $model = Barangay::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Barangays (SK)';

    protected static ?string $modelLabel = 'Barangay';

    protected static ?string $pluralModelLabel = 'Barangays (SK)';

    protected static string|\UnitEnum|null $navigationGroup = 'Local Councils';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return "Barangay {$record->name} ({$record->municipality->name})";
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['municipality']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('municipality_id')
                    ->relationship('municipality', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                        if (!$state) {
                            return;
                        }
                        $municipality = \App\Models\Municipality::find($state);
                        if ($municipality) {
                            $random = str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT);
                            $set('code', $municipality->code . $random);
                        }
                    }),
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\Hidden::make('code')->required(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SkOfficialsRelationManager::class,
            DocumentSubmissionsRelationManager::class,
        ];
    }

    public static function getRecordRouteKeyName(): ?string
    {
        return 'code';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();
        if (! $user instanceof \App\Models\User) {
            return $query;
        }
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            return $query;
        }
        if ($user->hasRole('municipal') && $user->municipality_id) {
            return $query->where('municipality_id', $user->municipality_id);
        }
        return $query;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(
                fn(Model $record): string => Pages\EditBarangay::getUrl([$record->code]),
            )
            ->groups([
                Group::make('municipality.name')
                    ->label('Municipality')
                    ->collapsible(),
            ])
            ->collapsedGroupsByDefault()
            ->defaultSort('municipality.name', 'asc')
            ->defaultGroup('municipality.name')
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('municipality.name')->label('Municipality')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('code')->searchable()->sortable(),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBarangays::route('/'),
            'create' => Pages\CreateBarangay::route('/create'),
            'edit' => Pages\EditBarangay::route('/{record}/edit'),
        ];
    }
}
