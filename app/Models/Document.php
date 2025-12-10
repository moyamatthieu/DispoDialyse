<?php

namespace App\Models;

use App\Enums\TypeDocumentEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle Document - Référentiel documentaire
 */
class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'category',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'version',
        'status',
        'tags',
        'author',
        'published_at',
        'expires_at',
        'restricted_to_roles',
        'view_count',
        'download_count',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'category' => TypeDocumentEnum::class,
            'tags' => 'array',
            'restricted_to_roles' => 'array',
            'published_at' => 'date',
            'expires_at' => 'date',
            'view_count' => 'integer',
            'download_count' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeCategory($query, TypeDocumentEnum $category)
    {
        return $query->where('category', $category->value);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }
}