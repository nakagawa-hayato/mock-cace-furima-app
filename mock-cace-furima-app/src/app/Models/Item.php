<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = ['item_id','condition_id','name','bland','price', 'description','image']; // 必要に応じて

    protected $casts = ['is_sold' => 'boolean',];

        public function categories()
        {
            return $this->belongsToMany(Category::class);
        }

        public function user()
        {
            return $this->belongsTo(User::class);
        }

        public function condition()
        {
            return $this->belongsTo(Condition::class);
        }

        public function favorites()
        {
            return $this->hasMany(Favorite::class);
        }

        public function isFavoritedBy($user)
        {
            return $this->favorites()->where('user_id', $user->id)->exists();
        }

        public function comments()
        {
            return $this->hasMany(Comment::class);
        }

        public function orders()
        {
            return $this->hasMany(Order::class);
        }
}
