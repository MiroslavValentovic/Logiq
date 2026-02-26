<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'client_name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** Používatelia priradení k projektu (many-to-many). */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')->withTimestamps();
    }

    public function workLogs(): HasMany
    {
        return $this->hasMany(WorkLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Projekty, ktoré má daný používateľ priradené (cez project_user). */
    public function scopeForUser($query, int $userId)
    {
        return $query->whereHas('users', fn ($q) => $q->where('users.id', $userId));
    }
}
