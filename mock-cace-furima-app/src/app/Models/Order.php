<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'item_id','method','post_code', 'address', 'building',]; // 必要に応じて

        public function user()
        {
            return $this->belongsTo(User::class);
        }

        public function item()
        {
            return $this->belongsTo(Item::class);
        }

}
