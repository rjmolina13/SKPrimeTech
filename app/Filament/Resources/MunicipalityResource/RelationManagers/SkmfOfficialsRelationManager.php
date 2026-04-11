<?php

namespace App\Filament\Resources\MunicipalityResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;

class SkmfOfficialsRelationManager extends RelationManager
{
    protected static string $relationship = 'officials';

    protected static ?string $title = 'SKMF Officials';

    protected static ?string $modelLabel = 'SK Official';
    
    protected static ?string $pluralModelLabel = 'SK Officials';

    protected static ?string $recordTitleAttribute = 'first_name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('role')
                    ->options([
                        'SKMF Chairperson' => 'SKMF Chairperson',
                        'SKMF Vice Chairperson' => 'SKMF Vice Chairperson',
                        'SKMF Secretary' => 'SKMF Secretary',
                        'SKMF Treasurer' => 'SKMF Treasurer',
                        'SKMF Auditor' => 'SKMF Auditor',
                        'SKMF PRO' => 'SKMF PRO',
                        'SKMF Sgt. at Arms' => 'SKMF Sgt. at Arms',
                    ])
                    ->required()
                    ->default('SKMF Chairperson'),
                Forms\Components\TextInput::make('first_name')->required(),
                Forms\Components\TextInput::make('middle_name'),
                Forms\Components\TextInput::make('last_name')->required(),
                Forms\Components\TextInput::make('phone_number')
                    ->prefix('+63 🇵🇭')
                    ->tel(),
                Forms\Components\TextInput::make('email')->email(),
                Forms\Components\TextInput::make('facebook_url')->url()->label('Facebook Account Link'),
                
                Fieldset::make('Current Address')
                    ->schema([
                        Forms\Components\TextInput::make('current_house_no')->label('House No./Street')->required(),
                        Forms\Components\Select::make('current_municipality_id')
                            ->relationship('currentMunicipality', 'name')
                            ->label('Municipality')
                            ->default(fn ($livewire) => $livewire->ownerRecord->id ?? null)
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('current_barangay_id')
                            ->relationship('currentBarangay', 'name')
                            ->label('Barangay')
                            ->searchable()
                            ->preload()
                            ->options(fn ($livewire) => \App\Models\Barangay::where('municipality_id', $livewire->ownerRecord->id ?? null)->pluck('name', 'id')),
                    ]),

                Forms\Components\Checkbox::make('is_same_as_current_address')
                    ->label('Permanent address is same as current address')
                    ->default(true)
                    ->live(),
                
                Group::make([
                     Forms\Components\TextInput::make('permanent_house_no')->label('House No./Street'),
                     Forms\Components\Select::make('permanent_municipality_id')
                        ->relationship('permanentMunicipality', 'name')
                        ->label('Municipality')
                        ->searchable()
                        ->preload()
                        ->live(),
                     Forms\Components\Select::make('permanent_barangay_id')
                        ->relationship('permanentBarangay', 'name')
                        ->label('Barangay')
                        ->searchable()
                        ->preload()
                        ->options(fn (Get $get) => \App\Models\Barangay::where('municipality_id', $get('permanent_municipality_id'))->pluck('name', 'id')),
                ])->visible(fn (Get $get) => ! $get('is_same_as_current_address')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('first_name')
            ->columns([
                Tables\Columns\TextColumn::make('role')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('first_name')->label('First Name')->searchable(),
                Tables\Columns\TextColumn::make('last_name')->label('Last Name')->searchable(),
                Tables\Columns\TextColumn::make('phone_number')->label('Phone'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'like', 'SKMF%'));
    }
}
