<?php
// app/Http/Controllers/RatingController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Rating;
use App\Models\Conversation;
use App\Models\Item;

class RatingController extends Controller
{
    /**
     * 評価の保存（AJAX）
     * POST /items/{item}/ratings
     *
     * ロジック:
     * - rating レコードは常に保存する
     * - conversation_id が渡されていればそれを使い、なければ item + 参加者から探索する
     * - conversation が見つかれば:
     *     - rater が seller の場合 -> conversation->markRated(true) を呼ぶ（is_rated = true, also mark completed）
     *     - rater が buyer の場合  -> conversation の is_rated は変更しない（既存ロジックに合わせる）
     */
    public function store(Request $request, Item $item)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'ログインが必要です。'], 403);
        }

        $validator = Validator::make($request->all(), [
            'score' => 'required|integer|min:1|max:5',
            'rated_user_id' => 'required|exists:users,id',
            'conversation_id' => 'nullable|integer|exists:conversations,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => '入力エラー', 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        DB::beginTransaction();
        try {
            // 1) 評価レコードを保存（comment カラムは無い）
            $rating = Rating::create([
                'rated_user_id' => $data['rated_user_id'],
                'rater_user_id' => $user->id,
                'item_id' => $item->id,
                'score' => $data['score'],
            ]);

            // 2) 会話を特定
            $conversation = null;
            if (! empty($data['conversation_id'])) {
                $conversation = Conversation::find($data['conversation_id']);
            }

            if (! $conversation) {
                $conversation = Conversation::where('item_id', $item->id)
                    ->where(function ($q) use ($user, $data) {
                        // rater が参加しているか、rated_user が参加している会話を探す
                        $q->where(function ($q2) use ($user) {
                            $q2->where('seller_id', $user->id)
                               ->orWhere('buyer_id', $user->id);
                        })
                        ->orWhere(function ($q3) use ($data) {
                            $q3->where('seller_id', $data['rated_user_id'])
                               ->orWhere('buyer_id', $data['rated_user_id']);
                        });
                    })
                    ->orderByDesc('updated_at')
                    ->first();
            }

            // 3) 見つかったら、役割に応じて conversation フラグを更新
            if ($conversation) {
                // rater が出品者（seller）の場合のみ is_rated を立てる（ProfileController のクエリ前提）
                if ($conversation->seller_id === $user->id) {
                    // alsoMarkCompleted: true にするかは設計判断ですが
                    // 「出品者が評価したら会話を完了扱いにする」要望なので true にしておく
                    $conversation->markRated(true);
                } else {
                    // rater が購入者（buyer）だった場合は、既に購入者側の「取引完了」アクションが先にあるはずなので
                    // ここでは is_rated を触らず、冪等にしておく（ログだけ出す）
                    \Log::info('RatingController: buyer submitted rating (conversation unchanged)', [
                        'conversation_id' => $conversation->id,
                        'rater_user_id' => $user->id,
                    ]);
                }
            } else {
                // conversation が見つからない場合はログを残して進める（後で手動で調査可能）
                \Log::warning('RatingController: conversation not found when saving rating', [
                    'item_id' => $item->id,
                    'rater_user_id' => $user->id,
                    'rated_user_id' => $data['rated_user_id'],
                    'payload' => $data,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => '評価を保存しました。',
                'rating_id' => $rating->id,
                'redirect' => url('/mypage?tab=transactions'),
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Rating store failed: '.$e->getMessage(), ['input' => $request->all()]);
            return response()->json(['message' => '評価保存中にエラーが発生しました。'], 500);
        }
    }
}
