<?php

namespace App\Http\Controllers;

use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Item;

class StripeWebhookController extends Controller
{
    /**
     * Stripe Webhook ハンドラー
     * Stripe からの通知で決済確定を受け取る
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        // 支払い完了
        if ($event->type === 'payment_intent.succeeded') {
            $intent = $event->data->object;

            // metadata から必要情報を取得
            $item_id  = $intent->metadata->item_id ?? null;
            $user_id  = $intent->metadata->user_id ?? null;
            $postcode = $intent->metadata->sending_postcode ?? '';
            $address  = $intent->metadata->sending_address ?? '';
            $building = $intent->metadata->sending_building ?? '';

            if ($item_id && $user_id) {
                $item = Item::find($item_id);

                if ($item && !$item->is_sold) {
                    // 商品を売却済みに更新
                    $item->is_sold = true;
                    $item->save();

                    // Order 作成
                    Order::create([
                        'user_id'          => $user_id,
                        'item_id'          => $item_id,
                        'sending_postcode' => $postcode,
                        'sending_address'  => $address,
                        'sending_building' => $building,
                    ]);
                }
            }
        }

        return response('Webhook received', 200);
    }
}
