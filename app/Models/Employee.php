<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $casts = [
        'start_date' => 'date',
    ];

    public const SPECIALTIES = [
        'reception'  => 'Raw Material Reception',
        'sorting'    => 'Sorting',
        'roasting'   => 'Roasting',
        'milling'    => 'Milling',
        'packaging'  => 'Packaging',
        'sales'      => 'Sales',
    ];

    protected $fillable = [
        'full_name',
        'national_id',
        'phone_number',
        'gender',
        'province',
        'district',
        'position',
        'start_date',
        'specialties',
    ];

    public function hasSpecialty(string $key): bool
    {
        if (empty($this->specialties)) return false;
        return in_array($key, explode(',', $this->specialties), true);
    }

    public function specialtiesList(): array
    {
        if (empty($this->specialties)) return [];
        return array_filter(explode(',', $this->specialties));
    }

}
