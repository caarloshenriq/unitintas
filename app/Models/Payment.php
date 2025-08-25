<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id','amount','payment_method','status',
    ];

    public function order(){ return $this->belongsTo(Order::class); }
}
