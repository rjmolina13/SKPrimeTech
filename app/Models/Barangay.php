<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    protected $fillable = ['municipality_id', 'name', 'code'];

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function officials()
    {
        return $this->hasMany(SkOfficial::class);
    }

    public function skChairperson()
    {
        return $this->hasOne(SkOfficial::class)->where('role', 'SK Chairperson');
    }

    public function skMembers()
    {
        return $this->hasMany(SkOfficial::class)->where('role', 'SK Member');
    }

    public function skSecretary()
    {
        return $this->hasOne(SkOfficial::class)->where('role', 'SK Secretary');
    }

    public function skTreasurer()
    {
        return $this->hasOne(SkOfficial::class)->where('role', 'SK Treasurer');
    }

    public function submissions()
    {
        return $this->morphMany(FormSubmission::class, 'record');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'code';
    }
}
