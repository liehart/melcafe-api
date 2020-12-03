<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'order_total', 'order_tax', 'order_delivery',
        'order_grand_total', 'address', 'lat', 'lon', 'distance',
        'payment_method', 'receipt', 'order_number'
    ];

    public function getCreatedAtAttribute() {
        if (!is_null($this->attributes['created_at'])) {
            return Carbon::parse($this->attributes['created_at'])->format("Y-m-d H:i:s");
        }
    }

    public function getUpdatedAtAttribute() {
        if (!is_null($this->attributes['updated_at'])) {
            return Carbon::parse($this->attributes['updated_at'])->format("Y-m-d H:i:s");
        }
    }

    public function order_status() {
        return $this->hasMany('App\Models\OrderStatus');
    }

    public function order_item() {
        return $this->hasMany('App\Models\OrderItem');
    }

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

}
