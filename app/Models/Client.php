<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'client_type',
        'role',           // new: 'client' or 'supplier'
        'supplier_code',  // new: unique code for suppliers
        'phone',
        'email',
        'address',
    ];

    /**
     * Check if this client is a supplier
     *
     * @return bool
     */
    public function isSupplier(): bool
    {
        return $this->role === 'supplier';
    }
}
