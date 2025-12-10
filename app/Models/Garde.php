<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle Garde - Planning de garde et astreintes
 */
class Garde extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'personnel_id',
        'start_datetime',
        'end_datetime',
        'oncall_type',
        'category',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_datetime' => 'datetime',
            'end_datetime' => 'datetime',
        ];
    }

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isCurrent(): bool
    {
        return now()->between($this->start_datetime, $this->end_datetime)
            && $this->status === 'confirmed';
    }

    public function scopeCurrent($query)
    {
        return $query->where('start_datetime', '<=', now())
            ->where('end_datetime', '>=', now())
            ->where('status', 'confirmed');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_datetime', '>', now())
            ->where('status', '!=', 'cancelled');
    }
}