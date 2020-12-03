<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['menu_id', 'order_id', 'quantity'];

    /*
     * Eloquent Model
     */

    public function order() {
        return $this->belongsTo('App\Models\Order');
    }

    public function menu() {
        return $this->belongsTo('App\Models\Menu');
    }
}
