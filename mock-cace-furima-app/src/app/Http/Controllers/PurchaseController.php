<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;

class PurchaseController extends Controller
{
    public function purchase($item_id)
    {
        $item = Item::findOrFail($item_id);
        return view('order', compact('item'));
    }


    public function store(Request $request, $item_id)
    {
        $item = Item::findOrFail($item_id);

        // 売却済みの場合はリダイレクト
        if ($item->is_sold) {
        return redirect('/')->with('status', 'この商品はすでに購入済みです');
    }

        $validated = $request->validate([
            'method' => 'required|string|max:255',
        ]);

        $post_code = session('order_post_code') ?? auth()->user()->profile->post_code;
        $address = session('order_address') ?? auth()->user()->profile->address;
        $building = session('order_building') ?? auth()->user()->profile->building;


            Stripe::setApiKey(env('STRIPE_SECRET'));

    if ($validated['method'] === 'カード支払い') {
        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => $item->name,
                    ],
                    'unit_amount' => $item->price,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('purchase.success', ['item_id' => $item->id]),
            'cancel_url'  => route('purchase.cancel', ['item_id' => $item->id]),
        ]);

        session([
            'order_item_id' => $item->id,
            'order_method' => 'カード支払い',
            'order_post_code' => $post_code,
            'order_address' => $address,
            'order_building' => $building,
        ]);

        return redirect($checkoutSession->url);
    }

    if ($validated['method'] === 'コンビニ支払い') {
        $paymentIntent = PaymentIntent::create([
            'amount' => $item->price * 100,
            'currency' => 'jpy',
            'payment_method_types' => ['konbini'],
            'description' => $item->name,
            'metadata' => [
                'user_id' => auth()->id(),
                'item_id' => $item->id,
                'post_code' => $post_code,
                'address'   => $address,
                'building'  => $building,
            ],
        ]);

        session([
        'order_item_id' => $item->id,
        'order_method' => 'コンビニ支払い',
        'order_post_code' => $post_code,
        'order_address' => $address,
        'order_building' => $building,
    ]);


        // 支払い情報の表示ページへ（要作成）
        return view('konbini', [
            'paymentIntent' => $paymentIntent,
            'item' => $item,
        ]);
    }

    return back()->with('error', '現在対応していない支払い方法です');
}

        //
    public function success($item_id)
    {
        $item = Item::findOrFail($item_id);

        if ($item->is_sold) {
            return redirect('/')->with('status', 'この商品はすでに購入済みです');
        }

        $item->is_sold = true;
        $item->save();

        Order::create([
            'user_id' => auth()->id(),
            'item_id' => $item->id,
            'method' => session('order_method', 'カード支払い'),
            'post_code' => session('order_post_code'),
            'address' => session('order_address'),
            'building' => session('order_building'),
        ]);

        session()->forget([
            'order_item_id',
            'order_method',
            'order_post_code',
            'order_address',
            'order_building',
        ]);

        return redirect('/')->with('status', '購入が完了しました');
    }

    public function cancel($item_id)
    {
        return redirect("/purchase/{$item_id}")->with('error', '決済がキャンセルされました');
    }


    public function edit($item_id)
    {
        $item = Item::findOrFail($item_id);
        return view('address', compact('item'));
    }


    public function update(Request $request, $item_id)
    {
        $validated = $request->validate([
            'post_code' => 'required|regex:/^\d{3}-\d{4}$/',
            'address' => 'required|string|max:255',
            'building' => 'nullable|string|max:255',
        ]);

        session([
        'order_post_code' => $validated['post_code'],
        'order_address' => $validated['address'],
        'order_building' => $validated['building']?? '',
    ]);

        return redirect("/purchase/{$item_id}");
    }

}
