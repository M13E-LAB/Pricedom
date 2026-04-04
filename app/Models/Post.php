<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    protected $fillable = [
        'user_id',
        'description',
        'image_path',
        'health_score',
        'health_analysis'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'health_score' => 'integer',
        'health_analysis' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }

    public function likes()
    {
        return $this->belongsToMany(User::class, 'post_likes')->withTimestamps();
    }

    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function isLikedBy(User $user): bool
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function getReactionsCountAttribute(): int
    {
        return $this->reactions()->count();
    }

    public function getReactionsByEmojiAttribute(): array
    {
        return $this->reactions()
            ->selectRaw('emoji, COUNT(*) as count')
            ->groupBy('emoji')
            ->pluck('count', 'emoji')
            ->toArray();
    }

    public function hasReactionFrom(User $user, string $emoji): bool
    {
        return $this->reactions()
            ->where('user_id', $user->id)
            ->where('emoji', $emoji)
            ->exists();
    }
}
