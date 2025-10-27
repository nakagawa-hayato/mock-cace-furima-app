<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Condition;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Http\Requests\ItemRequest;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    // 商品一覧画面（トップ画面）表示
public function index(Request $request)
{
    // --- 追加: 購入フロー用セッションをクリア ---
    session()->forget(['order_post_code', 'order_address', 'order_building', 'order_payment', 'order_item_id']);
    // ------------------------------------------------

    $tab = $request->query('tab');
    $keyword = $request->query('keyword');

    if ($tab === 'mylist') {
        if (!auth()->check()) {
            // 未ログインでmylistを要求 → 空配列で返す
            $items = collect(); // 空のコレクション
        } else {
            // BelongsToMany のクエリビルダを取得（favorites 経由で items を取得）
            // 注意: favorites と items を join するため、曖昧なカラム名があるとエラーになる。
            $query = auth()->user()->favoriteItems()->select('items.*');

            // 自分の出品商品を除外する（items.user_id を明示）
            $query->where('items.user_id', '!=', auth()->id());

            // キーワード検索があれば items.name を明示して検索
            if ($keyword) {
                $query->where('items.name', 'like', "%{$keyword}%");
            }

            $items = $query->get();
        }
    } else {
        $query = Item::query();

        if ($keyword) {
            $query->where('name', 'like', '%' . $keyword . '%');
        }

        // 自分の出品商品は除外（ログイン時のみ）
        if (auth()->check()) {
            $query->where('user_id', '!=', auth()->id());
        }

        $items = $query->get();
    }

    return view('index', compact('items', 'tab', 'keyword'));
}

    // 商品出品画面表示
    public function add()
    {
        $categories = Category::all();
        $conditions = Condition::all();

        return view('sell', compact('categories', 'conditions'));
    }

    // 商品出品登録処理
    public function store(ItemRequest $request)
    {
        $validated = $request->validated();
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'ログインしてください');
        }

        // 画像保存
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $path = $request->file('image')->store('images', 'public');
            $validated['image'] = $path;
        } else {
            return back()->withErrors(['image' => '有効な画像ファイルを選択してください'])->withInput();
        }

        DB::transaction(function () use ($validated, $user, &$item) {
            $item = $user->items()->create([
                'name' => $validated['name'],
                'brand' => $validated['brand'] ?? null,
                'price' => $validated['price'],
                'description' => $validated['description'],
                'condition_id' => $validated['condition_id'],
                'image' => $validated['image'],
            ]);

            // カテゴリ中間テーブル登録（空配列の場合はスキップ）
            if (!empty($validated['category'])) {
                $item->categories()->sync($validated['category']);
            }
        });

        return redirect('/')->with('status', '商品を登録しました');
    }

    // 商品詳細画面
    public function show($item_id)
    {
        session()->forget([
            'order_post_code', 'order_address', 'order_building',
            'order_payment', 'order_item_id'
        ]);

        $item = Item::with('favorites', 'comments.user.profile', 'categories', 'condition')
                    ->findOrFail($item_id);

        return view('detail', compact('item'));
    }
}
