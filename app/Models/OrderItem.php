<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id', 'product_id', 'quantity', 'price',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'price'    => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // subtotal calculado (não salvo)
    public function getSubtotalAttribute()
    {
        return (float)$this->quantity * (float)$this->price;
    }
}
