<?php

namespace App\Filament\Pages;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EditProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'My Profile';

    protected static ?string $title = 'My Profile';

    protected string $view = 'filament.pages.edit-profile';

    protected static ?string $slug = 'my-profile';

    protected static ?int $navigationSort = 11;

    public static function getLabel(): string
    {
        return 'My Profile';
    }

    public ?array $data = [];
    public ?array $passwordData = [];

    public function mount(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'avatar_type' => $user->avatar_type,
            'avatar_url' => $user->avatar_url,
            'avatar_background' => $user->avatar_background,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Profile Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->disabled(function () {
                                        /** @var \App\Models\User $user */
                                        $user = Auth::user();
                                        return ! $user->hasRole('super_admin');
                                    }),
                                TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                            ]),
                    ]),

                Section::make('Avatar')
                    ->description('Customize your profile picture.')
                    ->schema([
                        Select::make('avatar_type')
                            ->label('Avatar Source')
                            ->options([
                                'upload' => 'Upload Image',
                                'generated' => 'Generated Initials',
                                'icon' => 'Generated Icon',
                            ])
                            ->default('upload')
                            ->live()
                            ->afterStateUpdated(fn () => $this->validate()),

                        FileUpload::make('avatar_url')
                            ->label('Upload Avatar')
                            ->image()
                            ->avatar()
                            ->directory('avatars')
                            ->automaticallyResizeImagesMode('contain')
                            ->automaticallyResizeImagesToWidth('1920')
                            ->automaticallyResizeImagesToHeight('1080')
                            ->visible(fn ($get) => $get('avatar_type') === 'upload'),

                        Select::make('avatar_url') // Reusing avatar_url column to store icon name
                            ->label('Select Icon')
                            ->options([
                                'adventurer' => 'Adventurer',
                                'adventurer-neutral' => 'Adventurer Neutral',
                                'avataaars' => 'Avataaars',
                                'big-ears' => 'Big Ears',
                                'big-ears-neutral' => 'Big Ears Neutral',
                                'big-smile' => 'Big Smile',
                                'bottts' => 'Bottts',
                                'croodles' => 'Croodles',
                                'croodles-neutral' => 'Croodles Neutral',
                                'fun-emoji' => 'Fun Emoji',
                                'icons' => 'Icons',
                                'identicon' => 'Identicon',
                                'initials' => 'Initials',
                                'lorelei' => 'Lorelei',
                                'lorelei-neutral' => 'Lorelei Neutral',
                                'micah' => 'Micah',
                                'miniavs' => 'Miniavs',
                                'open-peeps' => 'Open Peeps',
                                'personas' => 'Personas',
                                'pixel-art' => 'Pixel Art',
                                'pixel-art-neutral' => 'Pixel Art Neutral',
                            ])
                            ->default('adventurer')
                            ->visible(fn ($get) => $get('avatar_type') === 'icon')
                            ->required(fn ($get) => $get('avatar_type') === 'icon'),

                        ColorPicker::make('avatar_background')
                            ->label('Background Color')
                            ->default('#000000')
                            ->visible(fn ($get) => in_array($get('avatar_type'), ['generated', 'icon'])),
                    ]),
                
                Section::make('Update Password')
                    ->schema([
                        TextInput::make('current_password')
                            ->label('Current Password')
                            ->password()
                            ->requiredWith('new_password')
                            ->currentPassword()
                            ->autocomplete('current-password'),
                        TextInput::make('new_password')
                            ->label('New Password')
                            ->password()
                            ->autocomplete('new-password')
                            ->rule(Password::default()),
                        TextInput::make('new_password_confirmation')
                            ->label('Confirm New Password')
                            ->password()
                            ->same('new_password')
                            ->requiredWith('new_password')
                            ->autocomplete('new-password'),
                    ])->statePath('passwordData'),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $this->form->validate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $userData = $this->data;
        
        // Handle Password Update if provided
        if (!empty($this->passwordData['new_password'])) {
            $userData['password'] = Hash::make($this->passwordData['new_password']);
        }

        // Remove password fields from user update data if they exist in main data array (unlikely due to statePath but safe)
        unset($userData['current_password'], $userData['new_password'], $userData['new_password_confirmation']);

        $user->update($userData);

        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'update_profile',
            'description' => 'User updated their profile information',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Clear password fields
        $this->passwordData = [];
        $this->form->fill($this->data); // Refill main form
        
        Notification::make()
            ->title('Profile updated successfully')
            ->success()
            ->send();
    }
}
