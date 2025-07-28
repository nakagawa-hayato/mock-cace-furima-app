<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
use App\Http\Requests\ProfileRequest;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    //プロフィール画面表示
    public function index(Request $request)
    {
        $user = auth()->user();
        $profile = $user->profile;

        $tab = $request->query('tab', 'sell');  // デフォルトは「sell」

        if ($tab === 'buy') {
        $items = $user->orders()->with('item')->get()->pluck('item');
        } else {
            $items = $user->items;
        }

    return view('profile', compact('profile', 'items', 'tab'));
    }



    // プロフィール編集画面表示
    public function edit(Request $request)
    {
        $user = auth()->user();
        $profile = $user->profile; // hasOne リレーション

        if ($request->session()->pull('just_registered')) {
            // 登録直後 → 空のプロフィールフォームを表示
            $profile = new \App\Models\Profile();
        } elseif (!$profile) {
            // プロフィール未作成 → 空のインスタンス（表示上便利なため）
            $profile = new \App\Models\Profile();
        }

        return view('edit', compact('profile'));
    }

    // プロフィール編集内容保存（新規 or 更新）
    public function update(ProfileRequest $request)
    {
        $user = auth()->user();
        // バリデーション済みデータを取得
        $validated = $request->validated();

        // 画像がアップロードされている場合
        if ($request->hasFile('profile_image')) {

            // ディレクトリ名を任意の名前で設定します
            $dir = 'images';

            // アップロードされたファイル名を取得
            $file_name = uniqid() . '_' . $request->file('profile_image')->getClientOriginalName();

            // imageディレクトリを作成し画像を保存
            // storage/app/public/任意のディレクトリ名/
            $request->file('profile_image')->storeAs('public/' . $dir, $file_name);

            $validated['image'] = $dir . '/' . $file_name;

        }

        // プロフィールがなければ作成、あれば更新
        $user->profile()->updateOrCreate([], $validated);

        return redirect('/')->with('status', 'プロフィールを保存しました');
    }


}

