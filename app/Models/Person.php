<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Heartbeat;

class Person extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'age'];
    public function heartbeats()
    {
        return $this->hasMany(Heartbeat::class);
    }
}
