<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Routine extends Model
{
    protected $fillable = [
        'user_id',
        'routine_category_id',
        'title',
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

    public function category()
    {
        return $this->belongsTo(RoutineCategory::class, 'routine_category_id');
    }

    public function likes()
    {
        return $this->hasMany(RoutineLike::class);
    }

    public function saves()
    {
        return $this->hasMany(SavedRoutine::class);
    }

    public function reactions()
    {
        return $this->hasMany(RoutineReaction::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->where('status', 'active');
    }

    public function getDisplayTitleAttribute(): string
    {
        if (! empty($this->title)) {
            return $this->title;
        }

        return 'Community Wellness Routine';
    }
}
