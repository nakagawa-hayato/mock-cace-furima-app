<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Condition;
use App\Models\Category;
use App\Models\Item;
use App\Models\CategoryItem;
use App\Models\Favorite;
use App\Models\Comment;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    //商品一覧画面（トップ画面）表示作成
    public function index(Request $request)
    {
        $tab = $request->query('tab');
        $keyword = $request->query('keyword');

        if ($tab === 'mylist') {
            if (!auth()->check()) {
                // 未ログインでmylistを要求 → 空配列で返す
                $items = collect(); // 空のコレクション
            } else {
                // マイリスト（いいね済み）の商品だけ取得
                $items = auth()->user()->favoriteItems;
                if ($keyword) {
                    $items = $items->filter(function ($item) use ($keyword) {
                        return str_contains($item->name, $keyword);
                    });
                }
            }
        } else {
            $query = Item::query();

            if ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%');
            }

            // 自分の出品商品を除く（ログイン時のみ）
            if (auth()->check()) {
                $query->where('user_id', '!=', auth()->id());
            }

            $items = $query->get();
        }

        return view('index', compact('items', 'tab', 'keyword'));
    }


    //商品出品画面表示
    public function add(Request $request)
    {
        $user = auth()->user();
        $categories = Category::all();
        $conditions = Condition::all();


        return view('sell', compact('user','categories','conditions'));
    }

    //商品出品登録処理
    public function store(Request $request)
{
    $dir = 'images';
    $file_name = $request->file('image')->getClientOriginalName();
    $path = $request->file('image')->storeAs('public/' . $dir, $file_name);

    $item = new Item();
    $item->name = $request->name;
    $item->bland = $request->bland;
    $item->price = $request->price;
    $item->description = $request->description;
    $item->image = 'storage/' . $dir . '/' . $file_name;
    $item->condition_id = $request->condition_id;
    $item->user_id = auth()->id();
    $item->save();

    // 中間テーブル登録（安全）
    $categories = $request->input('category', []);
    foreach ($categories as $category_id) {
        $item->categories()->attach($category_id); // belongsToManyの前提
    }

    return redirect('/')->with('status', '商品を登録しました');
}


    public function show($item_id)
    {
        $item = Item::with('favorites','comments','categories','condition')->find($item_id);

            return view('detail',compact('item')); 
    }
}
