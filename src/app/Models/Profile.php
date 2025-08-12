<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'display_name', 'post_code', 'address', 'building', 'image'];

        // ユーザーとのリレーション（BelongsTo）
        public function user(): BelongsTo
        {
            return $this->belongsTo(User::class);
        }
}
