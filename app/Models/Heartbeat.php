<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Heartbeat extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'heart_rate', 'heart_beat', 'humidity'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
