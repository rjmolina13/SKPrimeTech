<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormDefinitionResource\Pages;
use App\Models\FormDefinition;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class FormDefinitionResource extends Resource
{
    protected static ?string $model = FormDefinition::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Report Templates';

    protected static ?string $modelLabel = 'Report Template';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports & Compliance';

    public static function getRecordRouteKeyName(): ?string
    {
        return 'guid';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Questions')
                            ->icon('heroicon-o-list-bullet')
                            ->schema([
                                Repeater::make('schema')
                                    ->label('Form Fields')
                                    ->schema([
                                        Grid::make(12)
                                            ->schema([
                                                TextInput::make('label')
                                                    ->label('Question')
                                                    ->placeholder('Enter your question here')
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                        if (! $get('name')) {
                                                            $slug = Str::slug($state, '_');
                                                            $suffix = Str::random(6);
                                                            $set('name', "{$slug}_{$suffix}");
                                                        }
                                                    })
                                                    ->columnSpan(8),

                                                Select::make('type')
                                                    ->label('Answer Type')
                                                    ->options([
                                                        'text' => 'Short Answer',
                                                        'textarea' => 'Paragraph',
                                                        'number' => 'Number',
                                                        'select' => 'Dropdown',
                                                        'radio' => 'Multiple Choice',
                                                        'checkbox' => 'Checkboxes',
                                                        'date' => 'Date',
                                                        'file' => 'File Upload',
                                                        'links' => 'Link/s',
                                                    ])
                                                    ->default('text')
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(function (Set $set) {
                                                        $set('options', []);
                                                        $set('preset_options', []);
                                                    })
                                                    ->columnSpan(4),

                                                Hidden::make('name')
                                                    ->required()
                                                    ->dehydrated(),

                                                Textarea::make('description')
                                                    ->label('Description (Optional)')
                                                    ->placeholder('Help text for the user')
                                                    ->rows(2)
                                                    ->columnSpanFull(),

                                                // Options for Select/Radio/Checkbox
                                                Repeater::make('options')
                                                    ->label('Options')
                                                    ->visible(fn(Get $get) => in_array($get('type'), ['select', 'radio', 'checkbox']))
                                                    ->schema([
                                                        Grid::make(2)
                                                            ->schema([
                                                                TextInput::make('label')
                                                                    ->label(fn(Get $get) => $get('../../type') === 'select' ? 'Option' : 'Option Label')
                                                                    ->required()
                                                                    ->live(onBlur: true)
                                                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                                        if ($get('../../type') === 'select') {
                                                                            $set('value', Str::slug((string) $state, '_'));

                                                                            return;
                                                                        }

                                                                        if (! $get('value')) {
                                                                            $set('value', Str::slug((string) $state, '_'));
                                                                        }
                                                                    }),
                                                                TextInput::make('value')
                                                                    ->label('Option Value')
                                                                    ->required()
                                                                    ->visible(fn(Get $get) => in_array($get('../../type'), ['radio', 'checkbox'])),
                                                                Hidden::make('value')
                                                                    ->visible(fn(Get $get) => $get('../../type') === 'select')
                                                                    ->dehydrated(),
                                                            ]),
                                                    ])
                                                    ->addActionLabel('Add Option')
                                                    ->grid(1)
                                                    ->defaultItems(1)
                                                    ->columnSpanFull(),

                                                Repeater::make('preset_options')
                                                    ->label('Preset Suggestions')
                                                    ->helperText('Users can pick from these suggestions or type their own answer.')
                                                    ->visible(fn(Get $get) => $get('type') === 'text')
                                                    ->schema([
                                                        TextInput::make('value')
                                                            ->label('Suggestion')
                                                            ->required(),
                                                    ])
                                                    ->addActionLabel('Add Suggestion')
                                                    ->grid(1)
                                                    ->columnSpanFull(),

                                                // Text/Number specific
                                                TextInput::make('placeholder')
                                                    ->label('Placeholder Text')
                                                    ->visible(fn(Get $get) => in_array($get('type'), ['text', 'textarea', 'number', 'email', 'url']))
                                                    ->columnSpan(6),

                                                TextInput::make('default_value')
                                                    ->label('Default Value')
                                                    ->visible(fn(Get $get) => in_array($get('type'), ['text', 'textarea', 'number', 'email', 'url']))
                                                    ->columnSpan(6),

                                                // Validation Rules
                                                Section::make('Validation & Logic')
                                                    ->schema([
                                                        Grid::make(2)
                                                            ->schema([
                                                                Toggle::make('required')
                                                                    ->label('Required')
                                                                    ->default(false),

                                                                Toggle::make('is_url')
                                                                    ->label('Must be a Link')
                                                                    ->visible(fn(Get $get) => in_array($get('type'), ['text', 'textarea']))
                                                                    ->default(false),

                                                                TextInput::make('min')
                                                                    ->label('Minimum (Value/Length)')
                                                                    ->visible(fn(Get $get) => in_array($get('type'), ['number', 'text'])),

                                                                TextInput::make('max')
                                                                    ->label('Maximum (Value/Length)')
                                                                    ->visible(fn(Get $get) => in_array($get('type'), ['number', 'text'])),
                                                            ]),

                                                        Grid::make(2)
                                                            ->schema([
                                                                Select::make('visible_if_field')
                                                                    ->label('Show Only If Field...')
                                                                    ->searchable()
                                                                    ->options(function (Get $get) {
                                                                        // Access the repeater array. 
                                                                        // Path: schema is the repeater name.
                                                                        // We are inside: schema -> item -> visible_if_field
                                                                        // $get('../../schema') should return the array of items
                                                                        $schema = $get('../../schema');
                                                                        $options = [];
                                                                        if (is_array($schema)) {
                                                                            foreach ($schema as $item) {
                                                                                if (!empty($item['name']) && !empty($item['label'])) {
                                                                                    // Exclude self? Hard to know self index here without $state path inspection.
                                                                                    // Just list all for now.
                                                                                    $options[$item['name']] = $item['label'];
                                                                                }
                                                                            }
                                                                        }
                                                                        return $options;
                                                                    }),

                                                                TextInput::make('visible_if_value')
                                                                    ->label('...Has Value')
                                                                    ->helperText(function (Get $get): string {
                                                                        $labels = static::getVisibilityValueSuggestions(
                                                                            $get('../../schema'),
                                                                            $get('visible_if_field'),
                                                                        );

                                                                        return filled($labels)
                                                                            ? 'Use the option label shown in the selected field. Dropdown internal values are generated automatically.'
                                                                            : 'Enter the exact answer that shows this field.';
                                                                    })
                                                                    ->datalist(fn(Get $get): array => static::getVisibilityValueSuggestions(
                                                                        $get('../../schema'),
                                                                        $get('visible_if_field'),
                                                                    ))
                                                                    ->visible(fn(Get $get) => !empty($get('visible_if_field'))),

                                                                Toggle::make('is_sub_question')
                                                                    ->label('Display Beside Parent (Sub-question)')
                                                                    ->visible(fn(Get $get) => !empty($get('visible_if_field')))
                                                                    ->default(false),
                                                            ]),
                                                    ])
                                                    ->collapsible()
                                                    ->collapsed()
                                                    ->columnSpanFull(),

                                                Section::make('Link Settings')
                                                    ->visible(fn(Get $get) => $get('type') === 'links')
                                                    ->schema([
                                                        Grid::make(2)
                                                            ->schema([
                                                                TextInput::make('min_links')
                                                                    ->label('Minimum Links')
                                                                    ->numeric()
                                                                    ->default(1)
                                                                    ->required(),
                                                                TextInput::make('max_links')
                                                                    ->label('Maximum Links')
                                                                    ->numeric()
                                                                    ->default(1)
                                                                    ->required(),
                                                            ]),
                                                    ])
                                                    ->columnSpanFull(),

                                                // File Upload specific
                                                Section::make('File Upload Settings')
                                                    ->visible(fn(Get $get) => $get('type') === 'file')
                                                    ->schema([
                                                        Select::make('max_size')
                                                            ->label('Max File Size')
                                                            ->options([
                                                                '1024' => '1MB',
                                                                '5120' => '5MB',
                                                                '10240' => '10MB',
                                                                '20480' => '20MB',
                                                            ])
                                                            ->default('5120')
                                                            ->required(),

                                                        Select::make('file_types_preset')
                                                            ->label('Allowed File Types')
                                                            ->options([
                                                                'documents' => 'Documents (PDF, DOC, DOCX)',
                                                                'excel' => 'Excel (XLS, XLSX)',
                                                                'images' => 'Images (JPG, PNG)',
                                                                'custom' => 'Custom',
                                                            ])
                                                            ->default('documents')
                                                            ->live(),

                                                        TagsInput::make('custom_file_types')
                                                            ->label('Custom Extensions (e.g., .pdf)')
                                                            ->placeholder('Add extension')
                                                            ->visible(fn(Get $get) => $get('file_types_preset') === 'custom')
                                                            ->required(fn(Get $get) => $get('file_types_preset') === 'custom'),
                                                    ])
                                                    ->columnSpanFull(),
                                            ]),
                                    ])
                                    ->collapsible()
                                    ->cloneable()
                                    ->reorderableWithButtons()
                                    ->itemLabel(fn(array $state): ?string => $state['label'] ?? 'New Question')
                                    ->addActionLabel('Add Question')
                                    ->columnSpanFull(),
                            ]),

                        Tabs\Tab::make('Settings')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('General Settings')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Template Name')
                                            ->required()
                                            ->maxLength(255),

                                        Select::make('scope')
                                            ->options([
                                                'barangay' => 'Barangay Only',
                                                'municipality' => 'Municipality Only',
                                                'both' => 'Both',
                                            ])
                                            ->required()
                                            ->default('both'),

                                        Select::make('frequency')
                                            ->options([
                                                'monthly' => 'Monthly',
                                                'quarterly' => 'Quarterly',
                                                'annual' => 'Annual',
                                                'one-time' => 'One-time',
                                            ])
                                            ->default('quarterly')
                                            ->required()
                                            ->live(),

                                        Select::make('frequency_option')
                                            ->label('Day of Month')
                                            ->options(array_combine(range(1, 31), range(1, 31)))
                                            ->visible(fn(Get $get) => $get('frequency') === 'monthly')
                                            ->required(fn(Get $get) => $get('frequency') === 'monthly'),

                                        DateTimePicker::make('deadline')
                                            ->label('Submission Deadline'),

                                        Toggle::make('is_active')
                                            ->label('Active')
                                            ->default(true),
                                    ])->columns(2),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(
                fn(\Illuminate\Database\Eloquent\Model $record): string => Pages\EditFormDefinition::getUrl([$record->guid]),
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('scope')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'barangay' => 'warning',
                        'municipality' => 'info',
                        'both' => 'success',
                    }),
                Tables\Columns\TextColumn::make('frequency')
                    ->badge(),
                Tables\Columns\TextColumn::make('deadline')
                    ->dateTime('m/d/Y h:i a')
                    ->sortable()
                    ->formatStateUsing(function ($state): string {
                        if (empty($state)) {
                            return '<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-danger-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>';
                        }
                        return '';
                    })
                    ->html(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date Created')
                    ->dateTime('m/d/Y h:i a')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit'),
                \Filament\Actions\ReplicateAction::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->iconButton()
                    ->tooltip('Duplicate')
                    ->beforeReplicaSaved(function (FormDefinition $replica, FormDefinition $record): void {
                        $replica->guid = null;
                        $replica->name = static::generateDuplicateTemplateName($record->name);
                        $replica->schema = static::duplicateSchemaWithUniqueNames($record->schema);
                    }),
                \Filament\Actions\DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Delete')
                    ->schema([
                        \Filament\Forms\Components\Checkbox::make('delete_submissions')
                            ->label('Also delete related submitted reports')
                            ->default(false),
                    ])
                    ->action(function (FormDefinition $record, array $data): void {
                        $shouldDeleteSubmissions = (bool) ($data['delete_submissions'] ?? false);
                        $hasSubmissions = $record->submissions()->exists();

                        if ($hasSubmissions && ! $shouldDeleteSubmissions) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Delete blocked')
                                ->body('This template has submitted reports. Enable the checkbox to delete them together.')
                                ->send();
                            return;
                        }

                        if ($shouldDeleteSubmissions) {
                            $record->submissions()->delete();
                        }

                        $record->delete();
                    }),
            ])
            ->toolbarActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ]);
    }

    protected static function generateDuplicateTemplateName(string $baseName): string
    {
        $candidate = "{$baseName} (Copy)";
        $counter = 2;

        while (FormDefinition::query()->where('name', $candidate)->exists()) {
            $candidate = "{$baseName} (Copy {$counter})";
            $counter++;
        }

        return $candidate;
    }

    protected static function duplicateSchemaWithUniqueNames(mixed $schema): array
    {
        if (! is_array($schema)) {
            return [];
        }

        $nameMap = [];

        foreach ($schema as $index => $field) {
            if (! is_array($field)) {
                continue;
            }

            $oldName = $field['name'] ?? null;
            $label = $field['label'] ?? 'question';
            $slug = Str::slug((string) $label, '_');
            $suffix = Str::random(6);
            $newName = "{$slug}_{$suffix}";

            $schema[$index]['name'] = $newName;

            if (is_string($oldName) && $oldName !== '') {
                $nameMap[$oldName] = $newName;
            }
        }

        foreach ($schema as $index => $field) {
            if (! is_array($field)) {
                continue;
            }

            $visibleIfField = $field['visible_if_field'] ?? null;

            if (is_string($visibleIfField) && isset($nameMap[$visibleIfField])) {
                $schema[$index]['visible_if_field'] = $nameMap[$visibleIfField];
            }
        }

        return $schema;
    }

    public static function normalizeSchemaForStorage(mixed $schema): array
    {
        if (! is_array($schema)) {
            return [];
        }

        foreach ($schema as $index => $field) {
            if (! is_array($field)) {
                continue;
            }

            $type = $field['type'] ?? 'text';

            if (in_array($type, ['select', 'radio', 'checkbox'])) {
                $schema[$index]['options'] = static::normalizeFieldOptions($field['options'] ?? [], $type);
            }
        }

        return $schema;
    }

    public static function getVisibilityValueSuggestions(mixed $schema, ?string $fieldName): array
    {
        if (! is_array($schema) || blank($fieldName)) {
            return [];
        }

        foreach ($schema as $field) {
            if (! is_array($field) || ($field['name'] ?? null) !== $fieldName) {
                continue;
            }

            return collect(static::normalizeFieldOptions($field['options'] ?? [], $field['type'] ?? null))
                ->pluck('label')
                ->values()
                ->all();
        }

        return [];
    }

    protected static function normalizeFieldOptions(mixed $options, ?string $type): array
    {
        if (! is_array($options)) {
            return [];
        }

        $usedValues = [];

        return collect($options)
            ->map(function (mixed $option) use ($type, &$usedValues): ?array {
                $label = is_array($option)
                    ? trim((string) ($option['label'] ?? $option['value'] ?? ''))
                    : trim((string) $option);

                if ($label === '') {
                    return null;
                }

                $preferredValue = is_array($option)
                    ? trim((string) ($option['value'] ?? ''))
                    : '';

                $value = static::makeUniqueOptionValue(
                    label: $label,
                    usedValues: $usedValues,
                    preferredValue: $type === 'select' ? null : $preferredValue,
                );

                return [
                    'label' => $label,
                    'value' => $value,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    protected static function makeUniqueOptionValue(string $label, array &$usedValues, ?string $preferredValue = null): string
    {
        $baseValue = Str::slug($preferredValue ?: $label, '_');

        if ($baseValue === '') {
            $baseValue = 'option';
        }

        $value = $baseValue;
        $suffix = 2;

        while (in_array($value, $usedValues, true)) {
            $value = "{$baseValue}_{$suffix}";
            $suffix++;
        }

        $usedValues[] = $value;

        return $value;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFormDefinitions::route('/'),
            'create' => Pages\CreateFormDefinition::route('/create'),
            'edit' => Pages\EditFormDefinition::route('/{record}/edit'),
        ];
    }
}
