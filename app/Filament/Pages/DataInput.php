<?php

namespace App\Filament\Pages;

use App\Models\Barangay;
use App\Models\FormDefinition;
use App\Models\FormSubmission;
use App\Models\Municipality;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View as SchemaView;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DataInput extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationLabel = 'Submit a Report';

    protected static ?string $title = 'Submit a Report';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports & Compliance';

    protected string $view = 'filament.pages.data-input';

    public ?string $scope = null;
    public ?int $municipality_id = null;
    public ?int $barangay_id = null;
    public ?int $form_definition_id = null;
    
    public ?array $data = [];

    protected array $activeSchema = [];

    public function mount()
    {
        $user = Auth::user();
        // Ensure user is instance of App\Models\User before calling hasRole
        if ($user instanceof \App\Models\User && $user->hasRole('municipal') && $user->municipality_id) {
            $this->scope = 'barangay'; // Default scope
            $this->municipality_id = $user->municipality_id;
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Selection')
                    ->schema([
                        Select::make('scope')
                            ->options([
                                'municipality' => 'Municipality',
                                'barangay' => 'Barangay',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                // Keep municipality_id if user is municipal role
                                /** @var \App\Models\User $user */
                                $user = Auth::user();
                                if ($user->hasRole('municipal') && $user->municipality_id) {
                                    $this->municipality_id = $user->municipality_id;
                                    $this->reset(['barangay_id', 'form_definition_id', 'data']);
                                } else {
                                    $this->reset(['municipality_id', 'barangay_id', 'form_definition_id', 'data']);
                                }
                            }),

                        Select::make('municipality_id')
                            ->label('Municipality')
                            ->options(Municipality::pluck('name', 'id'))
                            ->searchable()
                            ->required(fn () => $this->scope === 'barangay' || $this->scope === 'municipality')
                            ->visible(fn () => $this->scope)
                            ->live()
                            ->afterStateUpdated(fn () => $this->reset(['barangay_id', 'form_definition_id', 'data']))
                            ->disabled(function () {
                                /** @var \App\Models\User $user */
                                $user = Auth::user();
                                return $user->hasRole('municipal');
                            })
                            ->dehydrated(), // Ensure value is passed even when disabled

                        Select::make('barangay_id')
                            ->label('Barangay')
                            ->options(fn () => Barangay::where('municipality_id', $this->municipality_id)->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required(fn () => $this->scope === 'barangay')
                            ->visible(fn () => $this->scope === 'barangay' && $this->municipality_id)
                            ->live()
                            ->afterStateUpdated(fn () => $this->reset(['form_definition_id', 'data'])),

                        Select::make('form_definition_id')
                            ->label('Form')
                            ->options(fn () => FormDefinition::where('is_active', true)
                                ->where(fn ($query) => $query->where('scope', $this->scope)->orWhere('scope', 'both'))
                                ->pluck('name', 'id'))
                            ->required()
                            ->visible(fn () => ($this->scope === 'municipality' && $this->municipality_id) || ($this->scope === 'barangay' && $this->barangay_id))
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->data = [];
                                if ($state) {
                                    $definition = FormDefinition::find($state);
                                    if ($definition && is_array($definition->schema)) {
                                        foreach ($definition->schema as $field) {
                                            if (! empty($field['name'])) {
                                                if (($field['type'] ?? null) === 'links') {
                                                    if ($this->shouldRenderSingleLinkInput($field)) {
                                                        $this->data[$field['name']] = '';
                                                    } else {
                                                        $minimumLinks = max(0, intval($field['min_links'] ?? 1));
                                                        $this->data[$field['name']] = array_fill(
                                                            0,
                                                            $minimumLinks,
                                                            ['url' => ''],
                                                        );
                                                    }
                                                } else {
                                                    $this->data[$field['name']] = $field['default_value'] ?? null;
                                                }
                                            }
                                        }
                                    }
                                }
                            }),
                    ])->columns(2),

                Section::make('Data Entry')
                    ->schema(fn () => $this->getDynamicFormSchema())
                    ->columns(1)
                    ->visible(fn () => $this->form_definition_id)
                    ->statePath('data'), // Bind only this section to the $data array
            ]);
    }

    protected function getDynamicFormSchema(): array
    {
        if (! $this->form_definition_id) {
            return [];
        }

        $definition = FormDefinition::find($this->form_definition_id);
        if (! $definition || ! is_array($definition->schema)) {
            return [];
        }

        $components = [];
        $processedNames = [];
        $schema = $definition->schema;
        $this->activeSchema = $schema;

        foreach ($schema as $index => $fieldConfig) {
            $name = $fieldConfig['name'] ?? null;
            if (! $name || in_array($name, $processedNames)) continue;

            // Check if this field is a "Parent" to any subsequent field that is marked as `is_sub_question`.
            // We look ahead for ANY field that has visible_if_field == this name AND is_sub_question == true
            $subQuestions = [];
            foreach ($schema as $subIndex => $subConfig) {
                if ($subIndex <= $index) continue; // Only look ahead
                
                $subName = $subConfig['name'] ?? null;
                if (!$subName || in_array($subName, $processedNames)) continue;

                if (
                    !empty($subConfig['visible_if_field']) && 
                    $subConfig['visible_if_field'] === $name && 
                    !empty($subConfig['is_sub_question'])
                ) {
                    $subQuestions[] = $subConfig;
                    $processedNames[] = $subName; // Mark as processed so main loop skips it
                }
            }

            $mainComponent = $this->createFieldComponent($fieldConfig);
            $processedNames[] = $name;

            if (!empty($subQuestions)) {
                $columns = 1 + count($subQuestions);
                $gridSchema = array_merge([$mainComponent], array_map(fn($c) => $this->createFieldComponent($c), $subQuestions));
                
                $components[] = Grid::make([
                    'default' => 1,
                    'xl' => $columns,
                ])
                    ->schema($gridSchema)
                    ->columnSpanFull();
            } else {
                $components[] = $mainComponent->columnSpanFull();
            }
        }

        return $components;
    }

    protected function createFieldComponent(array $fieldConfig)
    {
        $type = $fieldConfig['type'] ?? 'text';
        $name = $fieldConfig['name'] ?? null;
        $label = $fieldConfig['label'] ?? null;
        $description = $fieldConfig['description'] ?? null;

        $options = $fieldConfig['options'] ?? [];
        $presetOptions = $this->getPresetOptions($fieldConfig);
        if (!empty($options) && isset($options[0]) && is_array($options[0])) {
            $options = collect($options)->pluck('label', 'value')->toArray();
        }

        $field = match ($type) {
            'text' => TextInput::make($name),
            'textarea' => Textarea::make($name),
            'number' => TextInput::make($name)->numeric(),
            'select' => Select::make($name)->options($options),
            'radio' => Radio::make($name)->options($options),
            'date' => DatePicker::make($name),
            'file' => FileUpload::make($name)
                ->key('file_upload_' . $name)
                ->disk('public')
                ->directory('form-uploads')
                ->visibility('public')
                ->maxSize(intval($fieldConfig['max_size'] ?? 5120))
                ->preserveFilenames()
                ->automaticallyResizeImagesMode('contain')
                ->automaticallyResizeImagesToWidth('1920')
                ->automaticallyResizeImagesToHeight('1080')
                ->openable()
                ->downloadable()
                ->hintAction(
                    Action::make('preview')
                        ->label('Preview')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalWidth('7xl')
                        ->modalContent(function ($record, $state) use ($name) {
                            $filePath = $state;
                            if (is_array($filePath)) $filePath = reset($filePath);
                            if (!$filePath || is_array($filePath)) return \Illuminate\Support\Facades\View::make('filament.components.file-preview-error', ['message' => 'No valid file found.']);
                            
                            $url = Storage::url($filePath);
                            $mime = 'application/octet-stream';
                            try {
                                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                                // Simple mime deduction
                                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) $mime = 'image/' . $ext;
                                elseif ($ext === 'pdf') $mime = 'application/pdf';
                                elseif ($ext === 'txt') $mime = 'text/plain';
                                else {
                                     /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                                     $disk = Storage::disk('public');
                                     $mime = $disk->mimeType($filePath);
                                }
                            } catch (\Exception $e) {}
                            
                            return \Illuminate\Support\Facades\View::make('filament.components.file-preview', [
                                'url' => $url,
                                'mime' => $mime,
                            ]);
                        })
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false)
                        ->visible(fn ($state) => !empty($state) && !is_array($state))
                )
                ->acceptedFileTypes(function() use ($fieldConfig) {
                    $preset = $fieldConfig['file_types_preset'] ?? 'documents';
                    $presets = [
                        'documents' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                        'excel' => ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
                        'powerpoint' => ['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'],
                        'images' => ['image/jpeg', 'image/png'],
                        'documents_images' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'],
                    ];
                    if ($preset === 'custom') {
                        // Custom logic reused... simplifying for brevity in replacement but keeping logic
                         $types = $fieldConfig['custom_file_types'] ?? [];
                         if (is_string($types)) $types = explode(',', $types);
                         return collect($types)->map(fn($t) => trim($t))->map(function($t) {
                             if (!str_contains($t, '/') && !str_starts_with($t, '.')) $t = '.' . $t;
                             return match($t) {
                                 '.pdf' => 'application/pdf',
                                 '.jpg', '.jpeg' => 'image/jpeg',
                                 '.png' => 'image/png',
                                 default => $t
                             };
                         })->unique()->values()->toArray();
                    }
                    return $presets[$preset] ?? $presets['documents'];
                })
                ->helperText(function() use ($fieldConfig) {
                        $size = intval($fieldConfig['max_size'] ?? 5120);
                        $mb = $size / 1024;
                        return "Maximum file size: {$mb}MB.";
                }),
            'checkbox' => !empty($options) ? CheckboxList::make($name)->options($options) : Checkbox::make($name),
            'links' => $this->shouldRenderSingleLinkInput($fieldConfig)
                ? TextInput::make($name)
                    ->url()
                    ->maxLength(2048)
                : Repeater::make($name)
                    ->schema([
                        TextInput::make('url')
                            ->hiddenLabel()
                            ->url()
                            ->maxLength(2048)
                            ->required(),
                    ])
                    ->minItems(max(0, intval($fieldConfig['min_links'] ?? 1)))
                    ->maxItems(intval($fieldConfig['max_links'] ?? 1) > 0 ? intval($fieldConfig['max_links']) : null)
                    ->addActionLabel('Add Link')
                    ->defaultItems(max(0, intval($fieldConfig['min_links'] ?? 1)))
                    ->reorderable(empty($fieldConfig['visible_if_field']))
                    ->addable(
                        (intval($fieldConfig['max_links'] ?? 1) <= 0)
                        || (intval($fieldConfig['max_links'] ?? 1) > max(0, intval($fieldConfig['min_links'] ?? 1))),
                    )
                    ->deletable(max(0, intval($fieldConfig['min_links'] ?? 1)) === 0),
            default => TextInput::make($name),
        };

        $field->label($label ?? $name);
        if ($type === 'links') {
            $field->label(($label ?? $name) . ' (Link URL)');
        }
        if ($description) $field->helperText($description);
        if (! empty($fieldConfig['required'])) $field->required();
        if (! empty($fieldConfig['placeholder'])) $field->placeholder($fieldConfig['placeholder']);
        if ($type !== 'links' && ! empty($fieldConfig['default_value'])) $field->default($fieldConfig['default_value']);
        
        // Handle min/max for different field types
        if (in_array($type, ['number'])) {
            if (! empty($fieldConfig['min'])) $field->minValue($fieldConfig['min']);
            if (! empty($fieldConfig['max'])) $field->maxValue($fieldConfig['max']);
        }

        // Make fields live for reactivity
        if (in_array($type, ['select', 'radio', 'checkbox', 'toggle'])) {
            $field->live();
        } elseif (!in_array($type, ['file', 'links'])) {
            // Repeater (links) and FileUpload don't support live(onBlur) in the same way or might not need it
            $field->live(onBlur: true);
        }

        // Apply visibility logic
        if (! empty($fieldConfig['visible_if_field']) && isset($fieldConfig['visible_if_value'])) {
            $field->visible(function (Get $get) use ($fieldConfig) {
                $targetField = $fieldConfig['visible_if_field'];
                $targetValue = $fieldConfig['visible_if_value'];
                $currentValue = $get($targetField);

                return $this->matchesVisibleIfValue($targetField, $targetValue, $currentValue);
            });
        }

        if ($type === 'text' || $type === 'textarea') {
            if (! empty($fieldConfig['min'])) $field->minLength($fieldConfig['min']);
            if (! empty($fieldConfig['max'])) $field->maxLength($fieldConfig['max']);
            if (! empty($fieldConfig['is_url'])) $field->url();
        }

        if (($type === 'text') && ! empty($presetOptions)) {
            $field->belowContent(
                SchemaView::make('filament.components.short-answer-suggestions')
                    ->viewData([
                        'fieldName' => $name,
                        'statePath' => "data.{$name}",
                        'suggestions' => $presetOptions,
                    ]),
            );
        }

        return $field;
    }

    protected function getPresetOptions(array $fieldConfig): array
    {
        $presetOptions = $fieldConfig['preset_options'] ?? [];

        if (! is_array($presetOptions)) {
            return [];
        }

        return collect($presetOptions)
            ->map(function (mixed $option): ?string {
                if (is_array($option)) {
                    $option = $option['value'] ?? null;
                }

                $option = trim((string) ($option ?? ''));

                return $option !== '' ? $option : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function getOptionMap(array $fieldConfig): array
    {
        $options = $fieldConfig['options'] ?? [];

        if (! is_array($options)) {
            return [];
        }

        $usedValues = [];

        return collect($options)
            ->mapWithKeys(function (mixed $option) use (&$usedValues): array {
                $label = is_array($option)
                    ? trim((string) ($option['label'] ?? $option['value'] ?? ''))
                    : trim((string) $option);

                if ($label === '') {
                    return [];
                }

                $value = is_array($option)
                    ? trim((string) ($option['value'] ?? ''))
                    : '';

                $value = $this->makeUniqueOptionValue($label, $usedValues, $value);

                return [$value => $label];
            })
            ->all();
    }

    protected function makeUniqueOptionValue(string $label, array &$usedValues, ?string $preferredValue = null): string
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

    protected function matchesVisibleIfValue(string $targetField, mixed $expectedValue, mixed $currentValue): bool
    {
        $expectedValue = trim((string) ($expectedValue ?? ''));

        if (is_bool($currentValue)) {
            $currentValue = $currentValue ? '1' : '0';

            if (strtolower($expectedValue) === 'true') {
                $expectedValue = '1';
            }

            if (strtolower($expectedValue) === 'false') {
                $expectedValue = '0';
            }
        }

        if ((string) $currentValue === $expectedValue) {
            return true;
        }

        $targetFieldConfig = collect($this->activeSchema)
            ->first(fn (mixed $field): bool => is_array($field) && (($field['name'] ?? null) === $targetField));

        if (! is_array($targetFieldConfig)) {
            return false;
        }

        $optionLabel = $this->getOptionMap($targetFieldConfig)[(string) $currentValue] ?? null;

        return is_string($optionLabel) && $optionLabel === $expectedValue;
    }

    protected function shouldRenderSingleLinkInput(array $fieldConfig): bool
    {
        if (($fieldConfig['type'] ?? null) !== 'links') {
            return false;
        }

        $minLinks = max(0, intval($fieldConfig['min_links'] ?? 1));
        $maxLinks = intval($fieldConfig['max_links'] ?? 1);

        return ! empty($fieldConfig['visible_if_field'])
            && ! empty($fieldConfig['is_sub_question'])
            && $minLinks === 1
            && $maxLinks === 1;
    }

    protected function normalizeAndValidateUrl(mixed $value): array
    {
        $url = trim((string) ($value ?? ''));

        if ($url === '') {
            return [null, 'The URL is required.'];
        }

        if (preg_match('/\s/', $url)) {
            return [null, 'The URL must not contain spaces.'];
        }

        if (strlen($url) > 2048) {
            return [null, 'The URL must not be greater than 2048 characters.'];
        }

        if (str_contains($url, '<') || str_contains($url, '>') || str_contains($url, '"') || str_contains($url, "'") || str_contains($url, '&')) {
            return [null, 'The URL contains invalid characters.'];
        }

        if (! str_contains($url, '://')) {
            $url = 'https://' . $url;
        }

        $parts = parse_url($url);

        if (! is_array($parts)) {
            return [null, 'The URL format is invalid.'];
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));

        if (! in_array($scheme, ['http', 'https'], true)) {
            return [null, 'Only http and https URLs are allowed.'];
        }

        $host = (string) ($parts['host'] ?? '');

        if ($host === '') {
            return [null, 'The URL must include a valid host.'];
        }

        if (function_exists('idn_to_ascii')) {
            $asciiHost = idn_to_ascii($host, IDNA_DEFAULT);
            if ($asciiHost !== false && $asciiHost !== null) {
                $host = $asciiHost;
            }
        }

        $lowerHost = strtolower($host);

        if ($lowerHost === 'localhost' || str_ends_with($lowerHost, '.local') || str_ends_with($lowerHost, '.internal')) {
            return [null, 'The URL host is not allowed.'];
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $ipAllowed = filter_var(
                $host,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
            );

            if ($ipAllowed === false) {
                return [null, 'Private or reserved IP hosts are not allowed.'];
            }
        }

        $auth = '';

        if (isset($parts['user'])) {
            $auth .= $parts['user'];
            if (isset($parts['pass'])) {
                $auth .= ':' . $parts['pass'];
            }
            $auth .= '@';
        }

        $normalized = $scheme . '://' . $auth . $host;

        if (isset($parts['port'])) {
            $normalized .= ':' . $parts['port'];
        }

        $normalized .= $parts['path'] ?? '';

        if (isset($parts['query'])) {
            $normalized .= '?' . $parts['query'];
        }

        if (isset($parts['fragment'])) {
            $normalized .= '#' . $parts['fragment'];
        }

        if (strlen($normalized) > 2048) {
            return [null, 'The URL must not be greater than 2048 characters.'];
        }

        return [$normalized, null];
    }

    protected function normalizeAndValidateData(array $schema, array $data): array
    {
        $errors = [];
        $normalizedData = $data;

        foreach ($schema as $fieldConfig) {
            $name = $fieldConfig['name'] ?? null;
            $type = $fieldConfig['type'] ?? null;

            if (! $name || ! array_key_exists($name, $normalizedData)) {
                continue;
            }

            if ($type === 'links') {
                $items = $normalizedData[$name];
                $isSingleLinkInput = $this->shouldRenderSingleLinkInput($fieldConfig);

                if ($isSingleLinkInput) {
                    [$normalizedUrl, $error] = $this->normalizeAndValidateUrl($items);

                    if ($error !== null) {
                        $errors["data.$name"] = $error;
                        continue;
                    }

                    $normalizedData[$name] = [['url' => $normalizedUrl]];
                    continue;
                }

                if (! is_array($items)) {
                    $errors["data.$name"] = 'The links value must be a list.';
                    continue;
                }

                $minLinks = max(0, intval($fieldConfig['min_links'] ?? 1));
                $maxLinks = intval($fieldConfig['max_links'] ?? 1);
                $maxLinks = $maxLinks > 0 ? $maxLinks : null;
                $cleanItems = [];

                foreach ($items as $index => $item) {
                    $rawUrl = is_array($item) ? ($item['url'] ?? null) : $item;
                    [$normalizedUrl, $error] = $this->normalizeAndValidateUrl($rawUrl);

                    if ($error !== null) {
                        $errors["data.$name.$index.url"] = $error;
                        continue;
                    }

                    $cleanItems[] = ['url' => $normalizedUrl];
                }

                if (count($cleanItems) < $minLinks) {
                    $errors["data.$name"] = "At least {$minLinks} link(s) are required.";
                }

                if ($maxLinks !== null && count($cleanItems) > $maxLinks) {
                    $errors["data.$name"] = "No more than {$maxLinks} link(s) are allowed.";
                }

                $normalizedData[$name] = $cleanItems;
                continue;
            }

            if (! empty($fieldConfig['is_url'])) {
                [$normalizedUrl, $error] = $this->normalizeAndValidateUrl($normalizedData[$name]);

                if ($error !== null) {
                    $errors["data.$name"] = $error;
                    continue;
                }

                $normalizedData[$name] = $normalizedUrl;
            }
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return $normalizedData;
    }

    public function submit()
    {
        $this->form->validate();

        // Get dehydrated state (processed file paths, etc.)
        $state = $this->form->getState();
        // Extract only the dynamic form data which is nested under 'data' key due to statePath('data')
        $data = $state['data'] ?? [];
        $definition = FormDefinition::find($this->form_definition_id);

        if ($definition && is_array($definition->schema)) {
            $data = $this->normalizeAndValidateData($definition->schema, $data);
        }

        $recordId = $this->scope === 'barangay' ? $this->barangay_id : $this->municipality_id;
        $recordType = $this->scope === 'barangay' ? Barangay::class : Municipality::class;

        $submission = FormSubmission::create([
            'form_definition_id' => $this->form_definition_id,
            'record_id' => $recordId,
            'record_type' => $recordType,
            'data' => $data,
            'submitted_by' => Auth::id(),
            'status' => 'submitted', // Default status
        ]);

        \App\Models\ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create_submission',
            'description' => "Submitted report ID #{$submission->id} for " . class_basename($recordType),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        Notification::make()
            ->title('Saved successfully')
            ->success()
            ->send();

        // Optional: Reset form or redirect
        $this->data = [];
    }
}
