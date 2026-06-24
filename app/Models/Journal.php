<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'entry_date',
        'mood_log_id',
        'visibility',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function moodLog()
    {
        return $this->belongsTo(MoodLog::class, 'mood_log_id');
    }
}
