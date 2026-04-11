<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    protected $fillable = [
        'guid',
        'form_definition_id',
        'record_id',
        'record_type',
        'data',
        'submitted_by',
        'status',
        'remarks',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($submission) {
            $submission->guid = str_pad(mt_rand(1000, 999999), 6, '0', STR_PAD_LEFT);
        });
    }

    public function formDefinition()
    {
        return $this->belongsTo(FormDefinition::class);
    }

    public function record()
    {
        return $this->morphTo();
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'guid';
    }
}
