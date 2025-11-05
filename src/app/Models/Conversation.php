<?php
// app/Models/Conversation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conversation extends Model
{
    protected $fillable = [
        'item_id',
        'seller_id',
        'buyer_id',
        'last_message_at',
        'is_completed',
        'completed_at',
        'is_rated',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'is_rated' => 'boolean',
        'last_message_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * 取引を完了にする（冪等）
     */
    public function markCompleted(): void
    {
        if (! $this->is_completed) {
            $this->is_completed = true;
            $this->completed_at = now();
            $this->save();
        }
    }

    /**
     * 評価済みにする（必要であれば完了フラグも立てる）
     *
     * @param bool $alsoMarkCompleted if true, set is_completed = true as well
     */
    public function markRated(bool $alsoMarkCompleted = false): void
    {
        $changed = false;
        if (! $this->is_rated) {
            $this->is_rated = true;
            $changed = true;
        }
        if ($alsoMarkCompleted && ! $this->is_completed) {
            $this->is_completed = true;
            $this->completed_at = now();
            $changed = true;
        }
        if ($changed) {
            $this->save();
        }
    }
}
