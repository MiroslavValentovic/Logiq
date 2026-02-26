<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'work_date',
        'minutes',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('work_date', $year)->whereMonth('work_date', $month);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('work_date', $date);
    }
}
