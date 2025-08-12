<?php

namespace App\Http\Controllers;

use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use Illuminate\Http\Request;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        // Stripe署名検証（推奨：セキュリティ強化）
        $secret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $intent = $event->data->object;

            // item_id や user_id を metadata から取得して注文完了処理へ

            // metadata から情報を取得
            $item_id = $intent->metadata->item_id ?? null;
            $user_id = $intent->metadata->user_id ?? null;
            $post_code = $intent->metadata->post_code ?? '';
            $address   = $intent->metadata->address ?? '';
            $building  = $intent->metadata->building ?? '';

            if ($item_id && $user_id) {
                $item = \App\Models\Item::find($item_id);

                if ($item && !$item->is_sold) {
                    $item->is_sold = true;
                    $item->save();

                    \App\Models\Order::create([
                        'user_id' => $user_id,
                        'item_id' => $item_id,
                        'method' => 'コンビニ支払い',
                        'post_code' => $post_code,
                        'address'   => $address,
                        'building'  => $building,
                    ]);
                }
            }
        }

        return response('Webhook received', 200);
    }
}
