<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageRead extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $fillable = ['message_id','user_id','read_at'];

    protected $dates = ['read_at'];

    public function message() { return $this->belongsTo(Message::class); }
}
