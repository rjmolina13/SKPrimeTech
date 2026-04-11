<?php

namespace App\Filament\Pages;

use App\Models\Municipality;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class MyMunicipality extends Page implements HasForms
{
    use InteractsWithForms;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'My Municipality';

    protected static ?string $title = 'My Municipality Profile';

    protected string $view = 'filament.pages.my-municipality';

    protected static ?string $slug = 'my-municipality';

    protected static ?int $navigationSort = 10;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->hasRole('municipal') && $user->municipality_id;
    }

    public function mount(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $municipality = Municipality::find($user->municipality_id);

        if ($municipality) {
            $this->form->fill([
                'name' => $municipality->name,
                'code' => $municipality->code,
                'logo_path' => $municipality->logo_path,
                // Load existing officials or empty array
                'officials' => $municipality->officials->map(function ($official) {
                    return [
                        'name' => $official->name,
                        'role' => $official->role,
                        'contact_number' => $official->contact_number,
                    ];
                })->toArray(),
            ]);
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Municipality Details')
                    ->description('Update your municipality information.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->disabled(), // Name is usually locked or admin-managed
                                TextInput::make('code')
                                    ->label('Municipality Code')
                                    ->maxLength(255)
                                    ->disabled(),
                                FileUpload::make('logo_path')
                                    ->label('Logo')
                                    ->image()
                                    ->directory('municipality-logos')
                                    ->automaticallyResizeImagesMode('contain')
                                    ->automaticallyResizeImagesToWidth('1920')
                                    ->automaticallyResizeImagesToHeight('1080')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('SK Officials')
                    ->description('Manage SK Federation officials for your municipality.')
                    ->schema([
                        Repeater::make('officials')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('role')
                                    ->options([
                                        'SK Federation President' => 'SK Federation President',
                                        'SK Federation Vice President' => 'SK Federation Vice President',
                                        'SK Federation Secretary' => 'SK Federation Secretary',
                                        'SK Federation Treasurer' => 'SK Federation Treasurer',
                                        'SK Federation Auditor' => 'SK Federation Auditor',
                                        'SK Federation PRO' => 'SK Federation PRO',
                                        'SK Federation Sgt. at Arms' => 'SK Federation Sgt. at Arms',
                                    ])
                                    ->required(),
                                TextInput::make('contact_number')
                                    ->tel()
                                    ->maxLength(255),
                            ])
                            ->columns(3)
                            ->addActionLabel('Add Official')
                            ->reorderableWithButtons(),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $this->form->validate();

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $municipality = Municipality::find($user->municipality_id);

        if ($municipality) {
            // Update municipality details
            $municipality->update([
                'logo_path' => $this->data['logo_path'],
            ]);

            // Sync officials
            // Note: This is a simple delete-and-recreate approach for simplicity in this context.
            // For production with IDs, better sync logic is needed.
            // Assuming SkOfficial model has 'municipality_id'
            
            $municipality->officials()->delete(); // Clear existing
            
            foreach ($this->data['officials'] as $officialData) {
                $municipality->officials()->create([
                    'name' => $officialData['name'],
                    'role' => $officialData['role'],
                    'contact_number' => $officialData['contact_number'],
                ]);
            }

            Notification::make()
                ->title('Profile updated successfully')
                ->success()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Changes')
                ->submit('submit'),
        ];
    }
}
