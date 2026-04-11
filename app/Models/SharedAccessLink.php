<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SharedAccessLink extends Model
{
    public const DEFAULT_VISIBLE_SECTIONS = ['dashboard', 'records'];

    public const DEFAULT_RECORD_STATUSES = ['draft', 'submitted', 'under_review', 'approved', 'rejected'];

    protected $fillable = [
        'name',
        'token',
        'scope',
        'expires_at',
        'is_active',
        'access_count',
        'last_accessed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scope' => 'array',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
            'access_count' => 'integer',
            'last_accessed_at' => 'datetime',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->token)) {
                $model->token = Str::random(40);
            }

            $scope = is_array($model->scope) ? $model->scope : [];
            $scope['visible_sections'] = is_array($scope['visible_sections'] ?? null) && count($scope['visible_sections']) > 0
                ? $scope['visible_sections']
                : self::DEFAULT_VISIBLE_SECTIONS;
            $scope['record_statuses'] = is_array($scope['record_statuses'] ?? null) && count($scope['record_statuses']) > 0
                ? $scope['record_statuses']
                : self::DEFAULT_RECORD_STATUSES;
            $model->scope = $scope;

            if (empty($model->created_by) && \Illuminate\Support\Facades\Auth::check()) {
                $model->created_by = \Illuminate\Support\Facades\Auth::id();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function getVisibleSections(): array
    {
        $sections = $this->scope['visible_sections'] ?? self::DEFAULT_VISIBLE_SECTIONS;

        return is_array($sections) ? $sections : self::DEFAULT_VISIBLE_SECTIONS;
    }

    public function getVisibleRecordStatuses(): array
    {
        $statuses = $this->scope['record_statuses'] ?? self::DEFAULT_RECORD_STATUSES;

        return is_array($statuses) && count($statuses) > 0 ? $statuses : self::DEFAULT_RECORD_STATUSES;
    }

    public function trackAccess(?Carbon $accessedAt = null): void
    {
        $this->forceFill([
            'access_count' => ($this->access_count ?? 0) + 1,
            'last_accessed_at' => $accessedAt ?? now(),
        ])->save();
    }
}
