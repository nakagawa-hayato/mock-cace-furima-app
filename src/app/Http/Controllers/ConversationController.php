<?php
// app/Http/Controllers/ConversationController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail; // ← 追加
use App\Models\Conversation;
use App\Models\Message;
use App\Mail\TransactionCompletedMail; // ← 追加
use Carbon\Carbon;

class ConversationController extends Controller
{
    /**
     * 会話表示（サイドバー + 選択会話のメッセージ）
     */
    public function show(Request $request, Conversation $conversation)
    {
        $user = auth()->user();
        abort_unless($user, 403);

        // 最新の conversation を取得（確実性のため）
        $conversation = Conversation::with(['item', 'seller', 'buyer'])->findOrFail($conversation->id);

        // 権限チェック
        if ($conversation->seller_id !== $user->id && $conversation->buyer_id !== $user->id) {
            abort(403);
        }

        // サイドバー（同ユーザーの会話、現在の会話除外）
        $sidebar = Conversation::with('item')
            ->where(function ($q) use ($user) {
                $q->where('seller_id', $user->id)
                  ->orWhere('buyer_id', $user->id);
            })
            ->where('id', '!=', $conversation->id)
            ->orderByDesc('last_message_at')
            ->get();

        // メッセージ取得
        $messages = $conversation->messages()
            ->with(['user.profile'])
            ->orderBy('created_at', 'asc')
            ->get();

        // 自分以外のメッセージを既読にする
        $now = Carbon::now();
        foreach ($messages as $msg) {
            if ($msg->user_id === $user->id) continue;

            DB::table('message_reads')->updateOrInsert(
                ['message_id' => $msg->id, 'user_id' => $user->id],
                ['read_at' => $now]
            );
        }

        // サイドバー各会話の未読数を付与
        $sidebar->transform(function ($conv) use ($user) {
            $unread = DB::table('messages')
                ->leftJoin('message_reads', function ($join) use ($user) {
                    $join->on('messages.id', '=', 'message_reads.message_id')
                        ->where('message_reads.user_id', '=', $user->id);
                })
                ->where('messages.conversation_id', $conv->id)
                ->where('messages.user_id', '!=', $user->id)
                ->whereNull('message_reads.read_at')
                ->count();

            $conv->unread_count = (int) $unread;
            return $conv;
        });

        // 現在の会話の未読数
        $currentUnread = DB::table('messages')
            ->leftJoin('message_reads', function ($join) use ($user) {
                $join->on('messages.id', '=', 'message_reads.message_id')
                    ->where('message_reads.user_id', '=', $user->id);
            })
            ->where('messages.conversation_id', $conversation->id)
            ->where('messages.user_id', '!=', $user->id)
            ->whereNull('message_reads.read_at')
            ->count();
        $conversation->unread_count = (int) $currentUnread;

        return view('conversation', [
            'conversation' => $conversation,
            'messages' => $messages,
            'sidebar' => $sidebar,
        ]);
    }

    /**
     * 購入者が「取引を完了する」を押したとき（AJAX POST）
     */
    public function complete(Request $request, Conversation $conversation)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'ログインが必要です。'], 403);
        }

        // 購入者のみ操作可能
        if ($conversation->buyer_id !== $user->id) {
            return response()->json(['message' => 'あなたはこの操作を行えません。'], 403);
        }

        // 既に完了なら成功（冪等）
        if ($conversation->is_completed) {
            return response()->json(['message' => '既に完了済みです。'], 200);
        }

        try {
            $conversation->markCompleted();

            try {
                $seller = $conversation->seller;
                // seller が存在しメールアドレスがある場合のみ送信
                if ($seller && ! empty($seller->email)) {
                    // 同期送信：Mail::to(...)->send(...)
                    // キュー送信にしたい場合は ->queue(...) に変えてください（メールキュー設定が必要）
                    Mail::to($seller->email)
                        ->send(new TransactionCompletedMail($conversation->item, $user));
                } else {
                    \Log::warning('Conversation complete: seller missing or no email', [
                        'conversation_id' => $conversation->id,
                        'seller_id' => optional($seller)->id,
                    ]);
                }
            } catch (\Throwable $mailEx) {
                // メール送信で失敗してもトランザクション自体はコミットするがログを残す
                \Log::error('Conversation complete: mail send failed: '.$mailEx->getMessage(), [
                    'conversation_id' => $conversation->id,
                    'seller_id' => optional($conversation->seller)->id ?? null,
                ]);
                // 必要であればここで throw して全体をロールバックする選択も可
            }

            DB::commit();

            return response()->json(['message' => '取引を完了しました。'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Conversation complete failed: '.$e->getMessage(), ['id' => $conversation->id]);
            return response()->json(['message' => '取引完了処理でエラーが発生しました。'], 500);
        }
    }
}

