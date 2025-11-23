<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBadge extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'badge_type',
        'badge_level',
        'badge_name',
        'badge_emoji',
        'badge_color',
        'contributions_count'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
