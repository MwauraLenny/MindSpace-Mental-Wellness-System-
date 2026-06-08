<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoodLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'mood_value',
        'journal_note',
        'routine_shared',
        'logged_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
