<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;

class PurchaseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // ルートモデルバインディングされた Item オブジェクトか、item_id パラメータを受け取る
        $routeItem = $request->route('item');      // model binding: /purchase/{item}
        $itemId = $request->route('item_id');      // numeric id: /purchase/{item_id}

        $item = null;

        if ($routeItem instanceof Item) {
            $item = $routeItem;
        } elseif ($itemId) {
            $item = Item::find($itemId);
        }

        if (! $item) {
            // 商品がない場合は 404
            abort(404);
        }

        // 未ログインならログインへ（通常は auth ミドルで弾く想定）
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        // 出品者は購入できない
        if ($item->user_id === Auth::id()) {
            // ルートのパラメータ名は 'item_id' なのでそれに合わせる
            return redirect()->route('item.detail', ['item_id' => $item->id])
                             ->with('flash_alert', '出品者が購入することはできません');
        }

        // 既に売却済みの判定（モデルのフィールド名に合わせて調整）
        if ((isset($item->is_sold) && $item->is_sold) ||
            (method_exists($item, 'sold') && $item->sold())) {
            return redirect()->route('item.detail', ['item_id' => $item->id])
                             ->with('flash_alert', 'この商品は既に購入済みです');
        }

        // 全て OK
        return $next($request);
    }
}
