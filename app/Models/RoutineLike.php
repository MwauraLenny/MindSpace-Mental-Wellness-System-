<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutineLike extends Model
{
    protected $fillable = [
        'routine_id',
        'user_id',
    ];

    public function routine()
    {
        return $this->belongsTo(Routine::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
