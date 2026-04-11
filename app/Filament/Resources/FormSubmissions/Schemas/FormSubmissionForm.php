<?php

namespace App\Filament\Resources\FormSubmissions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

use Filament\Actions\Action;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;

class FormSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Submission Details')
                    ->schema([
                        TextInput::make('form_name_display')
                            ->label('Form Name')
                            ->dehydrated(false)
                            ->afterStateHydrated(fn (TextInput $component, $record) => $component->state($record->formDefinition->name ?? 'N/A'))
                            ->disabled(),
                        TextInput::make('scope_display')
                            ->label('Scope')
                            ->dehydrated(false)
                            ->afterStateHydrated(fn (TextInput $component, $record) => $component->state(ucfirst($record->formDefinition->scope ?? 'N/A')))
                            ->disabled(),
                        TextInput::make('submitted_by_display')
                            ->label('Submitted By')
                            ->dehydrated(false)
                            ->afterStateHydrated(function (TextInput $component, $record) {
                                if (!$record) {
                                    $component->state('N/A');
                                    return;
                                }
                                // Try to get name from relation (submitter), fallback to ID if relation fails
                                $name = $record->submitter->name ?? ($record->submitted_by ? "User ID: {$record->submitted_by}" : 'N/A');
                                $component->state($name);
                            })
                            ->disabled(),
                        TextInput::make('record_display')
                            ->label('Submitted For')
                            ->dehydrated(false)
                            ->afterStateHydrated(function (TextInput $component, $record) {
                                if (!$record || !$record->record) {
                                    $component->state('N/A');
                                    return;
                                }
                                $name = $record->record->name ?? 'N/A';
                                if ($record->record_type === 'App\Models\Barangay') {
                                     $name = $record->record->name . ' (' . ($record->record->municipality->name ?? 'Unknown') . ')';
                                }
                                $component->state($name);
                            })
                            ->disabled(),
                    ])->columns(2),

                Section::make('Status & Remarks')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'submitted' => 'Submitted',
                                'under_review' => 'Under Review',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required(),
                        Textarea::make('remarks')
                            ->columnSpanFull(),
                    ]),

                Section::make('Submitted Data')
                    ->schema(function (Get $get, $record) {
                        if (! $record || ! $record->formDefinition) {
                            return [
                                KeyValue::make('data')
                                    ->label('Raw Data')
                                    ->disabled(),
                            ];
                        }

                        $fields = [];
                        $schema = $record->formDefinition->schema;

                        if (is_array($schema)) {
                            foreach ($schema as $fieldConfig) {
                                $type = $fieldConfig['type'] ?? 'text';
                                $name = $fieldConfig['name'] ?? null;
                                $label = $fieldConfig['label'] ?? null;

                                if (! $name) continue;

                                $options = $fieldConfig['options'] ?? [];
                                if (!empty($options) && isset($options[0]) && is_array($options[0])) {
                                    $options = collect($options)->pluck('label', 'value')->toArray();
                                }

                                $field = match ($type) {
                                    'text' => Textarea::make("data.{$name}")->autosize()->rows(1),
                                    'textarea' => Textarea::make("data.{$name}")->autosize(),
                                    'number' => TextInput::make("data.{$name}")->numeric(),
                                    'select' => Select::make("data.{$name}")->options($options),
                                    'radio' => Radio::make("data.{$name}")->options($options),
                                    'checkbox' => !empty($options) ? CheckboxList::make("data.{$name}")->options($options) : Checkbox::make("data.{$name}"),
                                    'links' => Repeater::make("data.{$name}")
                                        ->schema([
                                            TextInput::make('url')
                                                ->label('Link URL')
                                                ->url()
                                        ])
                                        ->minItems(intval($fieldConfig['min_links'] ?? 1))
                                        ->maxItems(intval($fieldConfig['max_links'] ?? 1) > 0 ? intval($fieldConfig['max_links']) : null)
                                        ->addable(false)
                                        ->deletable(false),
                                    'date' => DatePicker::make("data.{$name}"),
                                    'file' => FileUpload::make("data.{$name}")
                                        ->disk('public') // Explicitly use public disk
                                        ->directory('form-uploads')
                                        ->openable()
                                        ->downloadable()
                                        ->visibility('public')
                                        ->hintAction(
                                            Action::make('preview')
                                                ->label('Preview')
                                                ->icon('heroicon-o-eye')
                                                ->color('info')
                                                ->modalWidth('7xl') // Increase modal width to max
                                                ->modalContent(function ($record) use ($name) {
                                                    $data = $record->data ?? [];
                                                    $filePath = $data[$name] ?? null;
                                                    
                                                    // Handle if path is array (multiple files) - take first
                                                    if (is_array($filePath)) {
                                                        $filePath = reset($filePath);
                                                    }

                                                    if (!$filePath || is_array($filePath)) { // Still array means invalid
                                                        return \Illuminate\Support\Facades\View::make('filament.components.file-preview-error', ['message' => 'No valid file found.']);
                                                    }
                                                    
                                                    // $url = Storage::disk('public')->url($filePath);
                                                    $url = Storage::url($filePath);
                                                    // $mime = Storage::disk('public')->mimeType($filePath);
                                                    $mime = 'application/octet-stream'; // Default fallback
                                                    try {
                                                        // Fallback to extension based check if mimeType fails or method doesn't exist
                                                        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                                                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                                            $mime = 'image/' . $ext;
                                                        } elseif ($ext === 'pdf') {
                                                            $mime = 'application/pdf';
                                                        } elseif (in_array($ext, ['doc', 'docx'])) {
                                                            $mime = $ext === 'doc' ? 'application/msword' : 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                                                        } elseif (in_array($ext, ['xls', 'xlsx'])) {
                                                            $mime = $ext === 'xls' ? 'application/vnd.ms-excel' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                                                        } elseif (in_array($ext, ['ppt', 'pptx'])) {
                                                            $mime = $ext === 'ppt' ? 'application/vnd.ms-powerpoint' : 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
                                                        } elseif (in_array($ext, ['txt', 'csv'])) {
                                                            $mime = 'text/plain';
                                                        } else {
                                                            // Try storage if available and no extension match
                                                            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                                                            $disk = Storage::disk('public');
                                                            $mime = $disk->mimeType($filePath);
                                                        }
                                                    } catch (\Exception $e) {
                                                        // Fallback to extension based check if mimeType fails
                                                        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                                                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                                            $mime = 'image/' . $ext;
                                                        } elseif ($ext === 'pdf') {
                                                            $mime = 'application/pdf';
                                                        } elseif (in_array($ext, ['doc', 'docx'])) {
                                                            $mime = $ext === 'doc' ? 'application/msword' : 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                                                        } elseif (in_array($ext, ['xls', 'xlsx'])) {
                                                            $mime = $ext === 'xls' ? 'application/vnd.ms-excel' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                                                        } elseif (in_array($ext, ['ppt', 'pptx'])) {
                                                            $mime = $ext === 'ppt' ? 'application/vnd.ms-powerpoint' : 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
                                                        } elseif (in_array($ext, ['txt', 'csv'])) {
                                                            $mime = 'text/plain';
                                                        }
                                                    }
                                                    
                                                    return \Illuminate\Support\Facades\View::make('filament.components.file-preview', [
                                                        'url' => $url,
                                                        'mime' => $mime,
                                                    ]);
                                                })
                                                ->modalSubmitAction(false)
                                                ->modalCancelAction(false)
                                                ->visible(fn ($record) => !empty($record->data[$name] ?? null) && !is_array($record->data[$name] ?? null)) // Hide if invalid/empty
                                        ),
                                    default => TextInput::make("data.{$name}"),
                                };

                                $field->label($label ?? $name)
                                    ->disabled(); // Make read-only

                                // Apply visibility logic identical to data entry if visible_if_field is set
                                if (! empty($fieldConfig['visible_if_field']) && isset($fieldConfig['visible_if_value'])) {
                                    $field->visible(function (Get $get) use ($fieldConfig) {
                                        $targetField = "data.{$fieldConfig['visible_if_field']}";
                                        $targetValue = $fieldConfig['visible_if_value'];
                                        $currentValue = $get($targetField);

                                        // Boolean normalization
                                        if (is_bool($targetValue)) {
                                            $targetValue = $targetValue ? '1' : '0';
                                        }
                                        if (is_bool($currentValue)) {
                                            $currentValue = $currentValue ? '1' : '0';
                                        }

                                        return (string) $currentValue === (string) $targetValue;
                                    });
                                }

                                $fields[] = $field;
                            }
                        }

                        return $fields;
                    }),
            ]);
    }
}
