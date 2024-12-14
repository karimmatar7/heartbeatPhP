<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    // Explicitly define the table name
    protected $table = 'person'; // <-- This should match your table name

    // Define the fillable fields
    protected $fillable = ['name', 'age', 'bodytemp', 'heart_rate'];
}
