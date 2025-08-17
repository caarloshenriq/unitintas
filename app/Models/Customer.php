<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name', 'email','document','phone',
        'address','city','state','zip',
    ];
    /**
     * Get the orders for the customer.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the addresses for the customer.
     */
    // public function addresses()
    // {
    //     return $this->hasMany(Address::class);
    // }
}
