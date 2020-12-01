<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'description', 'size', 'size_unit', 'price', 'image_path', 'deleted'
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
}
