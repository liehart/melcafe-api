<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'order_total', 'order_tax', 'order_delivery',
        'order_grand_total', 'address', 'lat', 'lon', 'distance',
        'payment_method', 'receipt'
    ];
}
