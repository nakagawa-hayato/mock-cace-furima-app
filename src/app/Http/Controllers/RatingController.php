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
            // 評価を保存
            $rating = Rating::create([
                'rated_user_id' => $data['rated_user_id'],
                'rater_user_id' => $user->id,
                'item_id' => $item->id,
                'score' => $data['score'],
            ]);

            // 該当の conversation を特定する
            $conversation = null;
            if (!empty($data['conversation_id'])) {
                $conversation = Conversation::find($data['conversation_id']);
            }

            if (! $conversation) {
                // item_id と参加者情報から可能な限り一意に検索
                $conversation = Conversation::where('item_id', $item->id)
                    ->where(function ($q) use ($user, $data) {
                        $q->where(function ($q2) use ($user) {
                            $q2->where('seller_id', $user->id)->orWhere('buyer_id', $user->id);
                        })
                        ->orWhere(function ($q3) use ($data) {
                            $q3->where('seller_id', $data['rated_user_id'])->orWhere('buyer_id', $data['rated_user_id']);
                        });
                    })
                    ->orderByDesc('updated_at')
                    ->first();
            }

            if ($conversation) {
                // 安全策：出品者が評価を送るケースは「取引を消したい（ユーザ要求）」ことが多いため、
                // is_rated を true にし、必要なら is_completed も true にする。
                // ここでは常に is_rated を true にする。
                // さらに、取引が未完了の場合、評価によって完了扱いにしたいなら第2引数 true にする。
                // 実務では「購入者が完了ボタンを押す」流れが正しいが、要望に合わせて alsoMarkCompleted を true にします。
                $alsoMarkCompleted = true;
                $conversation->markRated($alsoMarkCompleted);
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
