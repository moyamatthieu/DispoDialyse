<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle Message - Messagerie interne
 */
class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'subject',
        'body',
        'is_read',
        'read_at',
        'parent_message_id',
        'deleted_by_sender',
        'deleted_by_recipient',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'deleted_by_sender' => 'boolean',
            'deleted_by_recipient' => 'boolean',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function parentMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'parent_message_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Message::class, 'parent_message_id');
    }

    public function markAsRead(): bool
    {
        if ($this->is_read) {
            return false;
        }

        return $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('sender_id', $userId)->where('deleted_by_sender', false);
        })->orWhere(function ($q) use ($userId) {
            $q->where('recipient_id', $userId)->where('deleted_by_recipient', false);
        });
    }

    public function scopeInbox($query, int $userId)
    {
        return $query->where('recipient_id', $userId)
            ->where('deleted_by_recipient', false);
    }

    public function scopeSent($query, int $userId)
    {
        return $query->where('sender_id', $userId)
            ->where('deleted_by_sender', false);
    }
}