<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Saved extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'saved_item_id',
        'saved_item_type',
        'saved_at',
    ];

    protected function casts(): array
    {
        return [
            'saved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($saved) {
            if (empty($saved->saved_at)) {
                $saved->saved_at = now();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function savedItem()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include saved items for a certain type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('saved_item_type', $type);
    }

    /**
     * Scope a query to only include saved items for a certain user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if a user has already saved an item
     */
    public static function isSaved($userId, $itemId, $itemType)
    {
        return static::where('user_id', $userId)
            ->where('saved_item_id', $itemId)
            ->where('saved_item_type', $itemType)
            ->exists();
    }

    /**
     * Save an item for a user
     */
    public static function saveItem($userId, $itemId, $itemType)
    {
        if (!static::isSaved($userId, $itemId, $itemType)) {
            return static::create([
                'user_id' => $userId,
                'saved_item_id' => $itemId,
                'saved_item_type' => $itemType,
            ]);
        }

        return null; // Already saved
    }

    /**
     * Remove a saved item for a user
     */
    public static function removeSaved($userId, $itemId, $itemType)
    {
        return static::where('user_id', $userId)
            ->where('saved_item_id', $itemId)
            ->where('saved_item_type', $itemType)
            ->delete();
    }
}
