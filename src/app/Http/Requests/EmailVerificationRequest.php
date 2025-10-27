<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class EmailVerificationRequest extends FormRequest
{
    protected $unauthenticated_user;

    public function authorize()
    {
        // 1) ログインユーザーがいればそれを使う（一般的ケース）
        if (Auth::check()) {
            $this->unauthenticated_user = Auth::user();
        }

        // 2) セッションにユーザーオブジェクトがあればそれを使う
        if (! $this->unauthenticated_user) {
            $sessionUser = session()->get('unauthenticated_user');
            if ($sessionUser instanceof User) {
                $this->unauthenticated_user = $sessionUser;
            }
        }

        // 3) セッションに ID があれば取得する
        if (! $this->unauthenticated_user) {
            $sessionUserId = session()->get('unauthenticated_user_id');
            if ($sessionUserId) {
                $this->unauthenticated_user = User::find($sessionUserId);
            }
        }

        // 4) 最後の手段（route の id に紐づくユーザー） — ただし route が signed の場合のみ安全
        if (! $this->unauthenticated_user) {
            $routeId = $this->route('id');
            if ($routeId) {
                $this->unauthenticated_user = User::find($routeId);
            }
        }

        if (! $this->unauthenticated_user) {
            // ユーザーが特定できなければ許可しない
            return false;
        }

        // route の id と一致するか確認（厳密に）
        if (! hash_equals((string) $this->unauthenticated_user->getKey(), (string) $this->route('id'))) {
            return false;
        }

        // route の hash（メール検証用）と一致するか確認
        if (! hash_equals(sha1($this->unauthenticated_user->getEmailForVerification()), (string) $this->route('hash'))) {
            return false;
        }

        return true;
    }

    public function rules()
    {
        return [];
    }

    public function fulfill()
    {
        if (! $this->unauthenticated_user) {
            abort(403);
        }

        if (! $this->unauthenticated_user->hasVerifiedEmail()) {
            $this->unauthenticated_user->markEmailAsVerified();
            Auth::loginUsingId($this->unauthenticated_user->getKey());
        }
    }
}
