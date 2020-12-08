<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;


    protected $fillable = ['user_id', 'distance', 'income', 'current_order_id'];


    public function user() {
        return $this->belongsTo('App\Models\User');
    }
}
