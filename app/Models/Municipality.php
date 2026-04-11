<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    protected $fillable = ['name', 'code'];


    public function barangays()
    {
        return $this->hasMany(Barangay::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function skmfChairperson()
    {
        return $this->hasOne(SkOfficial::class)->where('role', 'SKMF Chairperson');
    }

    public function officials()
    {
        return $this->hasMany(SkOfficial::class);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'code';
    }
}
