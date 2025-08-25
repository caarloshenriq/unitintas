<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'description',
        'amount',
        'due_date',
        'status',
        'order_id',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}