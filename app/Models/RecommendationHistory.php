<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecommendationHistory extends Model
{
    use HasFactory;

    protected $table = 'recommendation_history';

    protected $fillable = [
        'user_id',
        'routine_id',
        'mood_log_id',
        'reason',
        'score',
        'shown_at',
        'acted_at',
        'action_taken',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'shown_at' => 'datetime',
            'acted_at' => 'datetime',
            'metadata' => 'array',
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

    public function moodLog()
    {
        return $this->belongsTo(MoodLog::class, 'mood_log_id');
    }
}
