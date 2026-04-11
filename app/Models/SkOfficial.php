<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkOfficial extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $fillable = [
        'municipality_id',
        'barangay_id',
        'role',
        'first_name',
        'middle_name',
        'last_name',
        'phone_number',
        'email',
        'facebook_url',
        'current_house_no',
        'current_barangay_id',
        'current_municipality_id',
        'is_same_as_current_address',
        'permanent_house_no',
        'permanent_barangay_id',
        'permanent_municipality_id',
    ];

    protected $casts = [
        'is_same_as_current_address' => 'boolean',
    ];

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    public function currentBarangay()
    {
        return $this->belongsTo(Barangay::class, 'current_barangay_id');
    }

    public function currentMunicipality()
    {
        return $this->belongsTo(Municipality::class, 'current_municipality_id');
    }

    public function permanentBarangay()
    {
        return $this->belongsTo(Barangay::class, 'permanent_barangay_id');
    }

    public function permanentMunicipality()
    {
        return $this->belongsTo(Municipality::class, 'permanent_municipality_id');
    }
}
