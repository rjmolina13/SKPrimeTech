<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormDefinition extends Model
{
    protected $table = 'form_definitions';

    protected $fillable = [
        'name',
        'schema',
        'scope',
        'is_active',
        'deadline',
        'frequency',
        'frequency_option',
    ];

    protected $casts = [
        'schema' => 'array',
        'is_active' => 'boolean',
        'deadline' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($form) {
            $form->guid = str_pad(mt_rand(1000, 999999), 6, '0', STR_PAD_LEFT);
        });
    }

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'guid';
    }
}
