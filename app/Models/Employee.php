<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'national_id',
        'phone_number',
        'gender',
        'province',
        'district',
        'position',
        'start_date',
    ];

}
