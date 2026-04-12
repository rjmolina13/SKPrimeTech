<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Support\Facades\Storage;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements HasAvatar, FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'municipality_id',
        'avatar_url',
        'avatar_type',
        'avatar_background',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->avatar_type === 'upload' && $this->avatar_url) {
            return Storage::url($this->avatar_url);
        }

        if ($this->avatar_type === 'generated') {
            $name = urlencode($this->name);
            $bg = $this->avatar_background ? str_replace('#', '', $this->avatar_background) : '000000';
            return "https://ui-avatars.com/api/?name={$name}&color=FFFFFF&background={$bg}";
        }

        if ($this->avatar_type === 'icon') {
            $style = $this->avatar_url ?: 'adventurer'; // Using avatar_url column to store icon style
            $seed = urlencode($this->name);
            $bg = $this->avatar_background ? str_replace('#', '', $this->avatar_background) : 'transparent';
            return "https://api.dicebear.com/9.x/{$style}/svg?seed={$seed}&backgroundColor={$bg}";
        }

        return null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
