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

        // タブの指定（デフォルト sell）
        $tab = $request->query('tab', 'sell');

        if ($tab === 'buy') {
            // 購入した商品のみ取得
            $items = $user->orders()->with('item')->get()->pluck('item');
        } else {
            // 出品した商品のみ取得
            $items = $user->items;
        }

    return view('profile', compact('profile', 'items', 'tab'));
    }



    // プロフィール編集画面表示
    public function edit(Request $request)
    {
        $user = auth()->user();
        $profile = $user->profile; // hasOne リレーション

        // 登録直後や未作成の場合は空インスタンスを返す
        if ($request->session()->pull('just_registered') || !$profile) {
            $profile = new Profile();
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
            // hashName() でユニークファイル名生成
            $file_name = $request->file('profile_image')->hashName();
            // public ディスクに保存
            $request->file('profile_image')->storeAs('public/' . $dir, $file_name);
            // 保存パスを $validated にセット
            $validated['image'] = $dir . '/' . $file_name;

        }

        // プロフィールがなければ作成、あれば更新
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id], // 条件を明示
            $validated);

        return redirect('/')->with('status', 'プロフィールを保存しました');
    }


}

