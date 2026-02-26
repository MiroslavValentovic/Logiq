<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'avatar_path',
        'email',
        'password',
        'is_admin',
    ];

    protected $appends = ['name'];

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
            'is_admin' => 'boolean',
        ];
    }

    /** Projekty, na ktoré je používateľ priradený (many-to-many). */
    public function assignedProjects(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_user')->withTimestamps();
    }

    public function workLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WorkLog::class);
    }

    public function monthlyReports(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MonthlyReport::class);
    }

    public function getNameAttribute(): string
    {
        return trim(($this->attributes['first_name'] ?? '') . ' ' . ($this->attributes['last_name'] ?? ''));
    }
}
