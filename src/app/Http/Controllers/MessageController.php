<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageRequest;
use App\Http\Requests\MessageUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Conversation;
use App\Models\Message;
use Carbon\Carbon;

class MessageController extends Controller
{
    /**
     * メッセージ作成（チャット投稿）
     * - バリデーションは MessageRequest（本文必須、max400、画像 jpeg/png）
     * - 画像は public ディスクの images/messages に保存
     * - conversation.last_message_at を更新
     */
    public function store(MessageRequest $request, Conversation $conversation)
    {
        $user = auth()->user();
        abort_unless($user, 403);

        // 会話の参加者であることを確認
        if ($conversation->seller_id !== $user->id && $conversation->buyer_id !== $user->id) {
            // もし buyer がまだ未設定 -> buyer に自動セットしても良い（今回は安全策として 403）
            abort(403);
        }

        $data = ['body' => $request->input('body')];

        // 画像があれば保存
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images/messages', 'public');
            $data['image'] = $path;
        }

        DB::transaction(function () use ($conversation, $user, $data) {
            $message = $conversation->messages()->create(array_merge($data, [
                'user_id' => $user->id,
            ]));

            // 会話の last_message_at を更新
            $conversation->last_message_at = Carbon::now();
            $conversation->save();

            // （オプション）自分向けの message_reads を作らない（受信者側で既読になるまで未読）
        });

        // web UI の場合はリダイレクトで戻す（AjaxであればJSONを返す）
        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok'], 201);
        }
        return redirect()->back();
    }

    /**
     * メッセージ編集
     */
    public function update(MessageUpdateRequest $request, Message $message)
    {
        $user = auth()->user();
        abort_unless($user, 403);

        if ($message->user_id !== $user->id) {
            abort(403);
        }

        $message->body = $request->input('body');
        $message->save();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'updated']);
        }
        return redirect()->back();
    }

    /**
     * メッセージ削除
     */
    public function destroy(Message $message)
    {
        $user = auth()->user();
        abort_unless($user, 403);

        if ($message->user_id !== $user->id) {
            abort(403);
        }

        // 画像ファイルが存在すれば削除（public ディスク）
        if ($message->image) {
            Storage::disk('public')->delete($message->image);
        }

        $message->delete();

        return redirect()->back();
    }

    /**
     * 特定メッセージを既読にする (AJAX 用。routes: POST /messages/{message}/read)
     */
    public function markRead(Message $message)
    {
        $user = auth()->user();
        abort_unless($user, 403);

        // 自分のメッセージを自分で既読にする必要はないが許容
        DB::table('message_reads')->updateOrInsert(
            ['message_id' => $message->id, 'user_id' => $user->id],
            ['read_at' => Carbon::now()]
        );

        return response()->json(['status' => 'read']);
    }
}

