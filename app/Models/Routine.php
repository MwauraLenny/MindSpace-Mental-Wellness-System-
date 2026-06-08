<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Routine extends Model
{
    protected $fillable = [
        'user_id',
        'mood_tag',
        'body',
        'is_anonymous',
        'upvote_count',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
