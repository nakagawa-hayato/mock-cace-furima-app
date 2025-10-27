<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\Facades\Auth;

class RegisteredUserController
{
    public function store(
        Request $request,
        CreateNewUser $creator
    ) {
        // CreateNewUser::create() はユーザーをバリデーションして作成して返す想定
        $user = $creator->create($request->all());

        // 登録イベント（メール送信等）を発火
        event(new Registered($user));

        // --- ここが重要 ---
        // 1) セッションにモデルそのものを put しておく（EmailVerificationRequest 側が期待）
        session()->put('unauthenticated_user', $user);

        // 互換のため ID も保存しておく（他の箇所が ID を期待している場合）
        session()->put('unauthenticated_user_id', $user->getKey());

        // もしすぐにログインさせたいなら Auth::login($user) を行う（必要なら）
        // Auth::login($user);

        return redirect()->route('verification.notice');
    }
}

