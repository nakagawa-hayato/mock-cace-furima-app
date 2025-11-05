<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = ['rated_user_id','rater_user_id','item_id','score'];

    public function ratedUser()
    {
        return $this->belongsTo(User::class,'rated_user_id');
    }

    public function raterUser()
    {
        return $this->belongsTo(User::class,'rater_user_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class,'item_id');
    }
}

