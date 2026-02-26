<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportUnlockRequest extends Model
{
    protected $fillable = [
        'user_id',
        'month',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function monthLabel(): string
    {
        return $this->month->translatedFormat('F Y');
    }
}
