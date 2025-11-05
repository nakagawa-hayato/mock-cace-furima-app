<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Cashier\Billable;
use App\Notifications\VerifyEmailNotification;
use App\Models\Message;
use App\Models\MessageRead;
use App\Models\Conversation;

class User extends Authenticatable implements \Illuminate\Contracts\Auth\MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, Billable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification());
    }

    // ---------- 既存リレーション ----------
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteItems()
    {
        return $this->belongsToMany(Item::class, 'favorites', 'user_id', 'item_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function ratingsReceived(): HasMany
    {
        return $this->hasMany(Rating::class, 'rated_user_id');
    }

    public function ratingsGiven(): HasMany
    {
        return $this->hasMany(Rating::class, 'rater_user_id');
    }

    /**
     * このユーザーが送信したメッセージ（messagesテーブルの user_id）
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
        // 利用例: $user->messages()->latest()->get();
    }

    /**
     * このユーザーの message_reads レコード（各メッセージの既読情報）
     */
    public function messageReads(): HasMany
    {
        return $this->hasMany(MessageRead::class);
        // 利用例: $user->messageReads()->whereNotNull('read_at')->count();
    }

    /**
     * 会話（Conversation）での出品者としての関係
     */
    public function conversationsAsSeller(): HasMany
    {
        return $this->hasMany(Conversation::class, 'seller_id');
    }

    /**
     * 会話（Conversation）での購入者としての関係
     */
    public function conversationsAsBuyer(): HasMany
    {
        return $this->hasMany(Conversation::class, 'buyer_id');
    }

    /**
     * ユーザーが参加している全会話を取得するクエリビルダー（便利メソッド）
     * 注: このメソッドは Eloquent relation オブジェクトではなく Query\Builder を返します。
     *      ->get() などをつなげて使ってください。
     *
     * 利用例:
     *   $convs = $user->conversations()->with('item')->get();
     */
    public function conversations()
    {
        return Conversation::where('seller_id', $this->id)
                           ->orWhere('buyer_id', $this->id);
    }


    public function unreadMessagesCount(): int
    {
        // 例示: messages テーブル / message_reads テーブル構造に依存するので
        // 必要に応じてクエリを調整してください。
        return MessageRead::where('user_id', $this->id)
                          ->whereNull('read_at')
                          ->count();
    }
}
