<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutineFeedback extends Model
{
    protected $fillable = [
        'user_id',
        'routine_id',
        'helped',
        'before_mood_value',
        'after_mood_value',
        'mood_delta',
    ];

    protected function casts(): array
    {
        return [
            'helped' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function routine()
    {
        return $this->belongsTo(Routine::class);
    }
}
