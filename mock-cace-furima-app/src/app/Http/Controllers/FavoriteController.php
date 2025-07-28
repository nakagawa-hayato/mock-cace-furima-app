<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function store(Request $request)
    {
        $item = Item::findOrFail($request->item_id);
        $user = auth()->user();

        // すでにお気に入り済みでなければ登録
        if (!$item->isFavoritedBy($user)) {
            Favorite::create([
                'user_id' => $user->id,
                'item_id' => $item->id,
            ]);
        }

        return back();
    }

    public function destroy(Request $request)
    {
        $item = Item::findOrFail($request->item_id);
        $user = auth()->user();

        // 該当のお気に入りを削除
        $item->favorites()->where('user_id', $user->id)->delete();

        return back();
    }
}
