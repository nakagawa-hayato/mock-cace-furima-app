<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\AddressRequest;
use App\Models\Item;
use App\Models\Order;            // 注文保存用モデル（存在しないなら適宜差し替え）
use Stripe\StripeClient;
use Exception;

class PurchaseController extends Controller
{
    /**
     * 購入フォーム表示
     */
    public function index($item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        // profile のフィールド名が post_code / postcode のどちらでも対応する
        $profile = $user->profile ?? null;
        $profile_post_code = $profile->post_code ?? ($profile->postcode ?? '');
        $profile_address   = $profile->address ?? '';
        $profile_building  = $profile->building ?? '';

        // セッションの値があれば優先（住所変更→戻る を反映するため）
        $address = [
            'post_code' => session('order_post_code', $profile_post_code),
            'address'   => session('order_address', $profile_address),
            'building'  => session('order_building', $profile_building),
        ];

        // 支払い方法の既選択を反映（任意）
        $paymentMethod = session('order_payment', null);

        return view('order', compact('item', 'user', 'address', 'paymentMethod'));
    }

    /**
     * Stripe Checkout で購入処理
     *
     * フォーム側で以下の name を使って送ること：
     * - post_code
     * - address
     * - building (任意)
     * - payment_method (card または konbini)
     */
    public function purchase(Request $request, $item_id)
    {
        $item = Item::findOrFail($item_id);

        // 売却済みチェック（モデルに合わせて）
        if (isset($item->is_sold) && $item->is_sold) {
            return redirect()->route('item.detail', ['item_id' => $item->id])
                             ->with('status', 'この商品はすでに購入済みです');
        }

        // 入力 / セッション から住所を取る（フォームにある名前に合わせる）
        $post_code = $request->input('post_code', session('order_post_code'));
        $address   = $request->input('address', session('order_address'));
        $building  = $request->input('building', session('order_building', ''));

        // 支払い方法
        $paymentMethod = $request->input('payment_method', session('order_payment', null));
        if (! in_array($paymentMethod, ['card', 'konbini'], true)) {
            return back()->withErrors(['payment_method' => '支払い方法を選択してください']);
        }

        // セッションに保存（success 時に使う）
        session([
            'order_item_id'   => $item->id,
            'order_post_code' => $post_code,
            'order_address'   => $address,
            'order_building'  => $building,
            'order_payment'   => $paymentMethod,
        ]);

        // Stripe クライアント（services.stripe.secret を利用）
        $stripe = new StripeClient(config('services.stripe.secret'));

        // Checkout セッション作成（選択した支払い方法のみ許可）
        $payment_types = $paymentMethod === 'card' ? ['card'] : ['konbini'];

        $checkoutSession = $stripe->checkout->sessions->create([
            'payment_method_types' => $payment_types,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => ['name' => $item->name],
                    'unit_amount' => $item->price,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('purchase.success', ['item_id' => $item->id]) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('purchase.cancel', ['item_id' => $item->id]),
            'metadata' => [
                'user_id'  => Auth::id(),
                'item_id'  => $item->id,
                'sending_post_code' => $post_code,
                'sending_address'   => $address,
                'sending_building'  => $building,
                'payment_method'    => $paymentMethod,
            ],
        ]);

        return redirect($checkoutSession->url);
    }

    /**
     * 住所の更新（セッションに保存して購入画面に戻す）
     * POST /purchase/address/{item_id}
     */
    public function update(AddressRequest $request, $item_id)
    {
        // AddressRequest により 'post_code' 等がバリデーション済み
        session([
            'order_post_code' => $request->post_code,
            'order_address'   => $request->address,
            'order_building'  => $request->building ?? '',
        ]);

        return redirect()->route('purchase.index', ['item_id' => $item_id]);
    }

    /**
     * Stripe redirect (success) -> 注文確定（簡易）
     * 本番では Webhook による確定を推奨します（this is demo/simple flow）
     */
    public function success(Request $request, $item_id)
    {
        $item = Item::findOrFail($item_id);

        // セッションから住所を取得
        $post_code = session('order_post_code');
        $address   = session('order_address');
        $building  = session('order_building', null);
        $user_id   = Auth::id();

        if (! $post_code || ! $address || ! $user_id) {
            // セッションが無い場合はリダイレクトして再試行させる
            return redirect()->route('purchase.index', ['item_id' => $item->id])
                             ->with('error', '配送先情報が揃っていません。購入をやり直してください。');
        }

        // 既に売却済みかを二重チェック
        if (isset($item->is_sold) && $item->is_sold) {
            return redirect('/')->with('status', 'この商品は既に購入済みです');
        }

        // 商品を売却済みに更新
        $item->is_sold = true;
        $item->save();

        // 注文を作成（Order モデルが無ければ SoldItem 等に置換）
        Order::create([
            'user_id'           => $user_id,
            'item_id'           => $item->id,
            'sending_postcode'  => $post_code,
            'sending_address'   => $address,
            'sending_building'  => $building,
        ]);

        // セッションの注文情報をクリア
        session()->forget(['order_item_id','order_post_code','order_address','order_building','order_payment']);

        return redirect('/')->with('flashSuccess', '決済が完了しました！');
    }

    /**
     * 決済キャンセル
     */
    public function cancel($item_id)
    {
        return redirect()->route('purchase.index', ['item_id' => $item_id])
                         ->with('error', '決済がキャンセルされました');
    }
}
