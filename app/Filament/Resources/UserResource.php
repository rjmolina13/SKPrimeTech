<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationLabel = 'System Accounts';

    protected static ?string $modelLabel = 'System Account';

    protected static string|\UnitEnum|null $navigationGroup = 'System Administration';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('email')->email()->required()->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->saved(fn (?string $state): bool => filled($state)),
                Forms\Components\Select::make('roles')
                    ->label('Roles')
                    ->options(Role::query()->pluck('name', 'name')->all())
                    ->multiple(),
                Forms\Components\Select::make('municipality_id')
                    ->relationship('municipality', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Municipality'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('roles.name')->label('Roles')->badge(),
                Tables\Columns\TextColumn::make('municipality.name')->label('Municipality')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('M j, Y h:i A')->toggleable(),
            ])
            ->filters([])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['roles', 'municipality']);
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user) {
            return $query;
        }
        if ($user->hasRole('super_admin')) {
            return $query;
        }
        if ($user->hasRole('municipal') && $user->municipality_id) {
            return $query->where(function ($q) use ($user) {
                $q->where('municipality_id', $user->municipality_id);
            });
        }
        // Fallback for non-super admins who are not municipal (e.g. system admins if any)
        return $query;
    }
}
