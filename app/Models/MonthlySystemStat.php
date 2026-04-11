<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlySystemStat extends Model
{
    protected $fillable = [
        'month_start',
        'stats',
        'captured_at',
    ];

    protected function casts(): array
    {
        return [
            'month_start' => 'date',
            'stats' => 'array',
            'captured_at' => 'datetime',
        ];
    }
}
