<?php

namespace App\Filament\Resources\SharedAccessLinks;

use App\Filament\Resources\SharedAccessLinks\Pages\ManageSharedAccessLinks;
use App\Models\SharedAccessLink;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Js;

class SharedAccessLinkResource extends Resource
{
    protected static ?string $model = SharedAccessLink::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static string|\UnitEnum|null $navigationGroup = 'Access Management';

    protected static ?string $navigationLabel = 'Shared Access';

    protected static ?string $modelLabel = 'Shared Access Link';

    protected static ?string $pluralModelLabel = 'Shared Access Links';

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = request()->user();
        return $user?->hasRole(['super_admin', 'admin']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('token')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Auto-generated upon creation.'),
                DateTimePicker::make('expires_at')
                    ->label('Expiration Date')
                    ->nullable(),
                Toggle::make('is_active')
                    ->default(true)
                    ->label('Active'),
                CheckboxList::make('scope.visible_sections')
                    ->label('Visible Sections')
                    ->options([
                        'dashboard' => 'Dashboard (Excluding System Stats)',
                        'records' => 'All Records',
                    ])
                    ->default(SharedAccessLink::DEFAULT_VISIBLE_SECTIONS)
                    ->required()
                    ->minItems(1)
                    ->columns(2),
                CheckboxList::make('scope.record_statuses')
                    ->label('Visible Record Statuses')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'under_review' => 'Under Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default(SharedAccessLink::DEFAULT_RECORD_STATUSES)
                    ->required()
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('token')
                    ->label('Public URL')
                    ->state(fn (SharedAccessLink $record): string => static::makePublicUrl($record->token))
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = (string) $column->getState();

                        if (strlen($state) <= (int) $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    }),
                TextColumn::make('scope.visible_sections')
                    ->label('Visible Sections')
                    ->badge()
                    ->separator(',')
                    ->toggleable(),
                TextColumn::make('scope.record_statuses')
                    ->label('Visible Record Statuses')
                    ->badge()
                    ->separator(',')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('access_count')
                    ->numeric()
                    ->sortable()
                    ->label('Access Count'),
                TextColumn::make('last_accessed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('open')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn (SharedAccessLink $record): string => static::makePublicUrl($record->token))
                    ->openUrlInNewTab()
                    ->iconButton()
                    ->tooltip('Open public page'),
                Action::make('copyLink')
                    ->icon(Heroicon::OutlinedClipboardDocument)
                    ->color('gray')
                    ->iconButton()
                    ->tooltip('Copy public URL')
                    ->actionJs(fn (SharedAccessLink $record): string => static::makeCopyLinkActionJs($record->token)),
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit')
                    ->extraModalFooterActions([
                        Action::make('regenerateToken')
                            ->label('Regenerate Token')
                            ->icon(Heroicon::OutlinedArrowPath)
                            ->color('warning')
                            ->requiresConfirmation()
                            ->action(function (SharedAccessLink $record): void {
                                $record->update(['token' => str()->random(40)]);

                                Notification::make()
                                    ->title('Token regenerated')
                                    ->success()
                                    ->send();
                            }),
                    ]),
                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Delete'),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSharedAccessLinks::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function makePublicUrl(string $token): string
    {
        return route('access.view', ['token' => $token]);
    }

    public static function makeTruncatedPublicUrl(string $token, int $limit = 52): string
    {
        return str(static::makePublicUrl($token))->limit($limit)->toString();
    }

    public static function makeCopyLinkActionJs(string $token): string
    {
        $url = Js::from(static::makePublicUrl($token));

        return <<<JS
        const url = {$url};
        if (! window.navigator?.clipboard) {
            new FilamentNotification()
                .title('Clipboard is not available')
                .danger()
                .send()
            return
        }

        window.navigator.clipboard.writeText(url)
            .then(() => {
                new FilamentNotification()
                    .title('Public URL copied')
                    .success()
                    .send()
            })
            .catch(() => {
                new FilamentNotification()
                    .title('Failed to copy public URL')
                    .danger()
                    .send()
            })
        JS;
    }
}
