<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
use App\Models\Item;
use App\Models\Order;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Http\Requests\ProfileRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * マイページ（プロフィール）表示
     * tab: sell | buy | transactions
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $profile = $user->profile;

        // タブの指定（デフォルト: 'sell'）
        $tab = $request->query('tab', 'sell');

        // rawItems は DB モデルのコレクション（Conversation か Item）
        $rawItems = collect();

        if ($tab === 'buy') {
            $orders = $user->orders()->with('item')->get();
            $rawItems = $orders->pluck('item')->filter();
        } elseif ($tab === 'transactions') {
            // 取引中タブの取得ロジック
            $convQuery = Conversation::with(['item'])
                ->where(function ($q) use ($user) {
                    $q->where('seller_id', $user->id)
                      ->orWhere('buyer_id', $user->id);
                });

            if (schema_has_columns('conversations', ['is_completed'])) {
                if (schema_has_columns('conversations', ['is_rated'])) {
                    $convQuery->where(function ($q2) use ($user) {
                        $q2->whereNull('conversations.is_completed')
                           ->orWhere('conversations.is_completed', 0)
                           ->orWhere(function ($q3) use ($user) {
                               $q3->where('conversations.is_completed', 1)
                                  ->where('conversations.is_rated', 0)
                                  ->where('conversations.seller_id', $user->id);
                           });
                    });
                } else {
                    $convQuery->where(function ($q2) {
                        $q2->whereNull('conversations.is_completed')
                           ->orWhere('conversations.is_completed', 0);
                    });
                }
            }

            $rawItems = $convQuery->orderByDesc('last_message_at')->get();
        } else {
            // 'sell'：自分の出品一覧
            $rawItems = $user->items()->get();
        }

        //
        // relevant items (user が出品した item + user が購入した item)
        //
        $myItemIds = $user->items()->pluck('id')->toArray();
        $boughtItemIds = $user->orders()->pluck('item_id')->toArray();
        $relevantItemIds = array_values(array_unique(array_merge($myItemIds, $boughtItemIds)));

        //
        // 未読集計
        //
        $conversationUnreadMap = [];
        $itemUnreadMap = [];

        try {
            if (class_exists(Conversation::class)
                && class_exists(Message::class)
                && schema_has_columns('messages', ['conversation_id'])
                && schema_has_columns('conversations', ['id', 'item_id'])) {

                $query = DB::table('messages')
                    ->join('conversations', 'messages.conversation_id', '=', 'conversations.id')
                    ->leftJoin('message_reads', function ($join) use ($user) {
                        $join->on('messages.id', '=', 'message_reads.message_id')
                             ->where('message_reads.user_id', '=', $user->id);
                    })
                    ->where('messages.user_id', '!=', $user->id)
                    ->whereNull('message_reads.read_at')
                    ->where(function ($q) use ($user) {
                        $q->where('conversations.seller_id', $user->id)
                          ->orWhere('conversations.buyer_id', $user->id);
                    });

                if (schema_has_columns('conversations', ['is_completed'])) {
                    $query->where(function ($q2) {
                        $q2->whereNull('conversations.is_completed')
                           ->orWhere('conversations.is_completed', 0);
                    });
                }

                $query->select('messages.conversation_id', 'conversations.item_id', DB::raw('count(messages.id) as cnt'))
                      ->groupBy('messages.conversation_id', 'conversations.item_id');

                $rows = $query->get();

                foreach ($rows as $r) {
                    $conversationUnreadMap[$r->conversation_id] = (int) $r->cnt;

                    if (! empty($r->item_id)) {
                        if (! isset($itemUnreadMap[$r->item_id])) {
                            $itemUnreadMap[$r->item_id] = 0;
                        }
                        $itemUnreadMap[$r->item_id] += (int) $r->cnt;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('ProfileController: unread aggregation failed (conversation path): ' . $e->getMessage());
            $conversationUnreadMap = [];
            $itemUnreadMap = [];
        }

        // フォールバック
        if (empty($conversationUnreadMap) && ! empty($relevantItemIds)) {
            try {
                if (class_exists(Message::class) && schema_has_columns('messages', ['item_id'])) {
                    $rows = DB::table('messages')
                        ->leftJoin('message_reads', function ($join) use ($user) {
                            $join->on('messages.id', '=', 'message_reads.message_id')
                                 ->where('message_reads.user_id', '=', $user->id);
                        })
                        ->where('messages.user_id', '!=', $user->id)
                        ->whereNull('message_reads.read_at')
                        ->whereIn('messages.item_id', $relevantItemIds)
                        ->select('messages.item_id', DB::raw('count(messages.id) as cnt'))
                        ->groupBy('messages.item_id')
                        ->get();

                    foreach ($rows as $r) {
                        $itemUnreadMap[$r->item_id] = (int) $r->cnt;
                    }
                }
            } catch (\Throwable $e) {
                Log::error('ProfileController: unread aggregation failed (item fallback): ' . $e->getMessage());
            }
        }

        //
        // viewItems を組み立てる
        //
        $viewItems = collect();
        foreach ($rawItems as $raw) {
            $isConversation = (is_object($raw) && (get_class($raw) === Conversation::class || strpos(get_class($raw), 'Conversation') !== false));

            if ($isConversation) {
                $conv = $raw;
                $theItem = $conv->item;
                $itemId = optional($theItem)->id;

                // 画像パス
                $imagePath = optional($theItem)->image;
                if (! empty($imagePath)) {
                    if (strpos($imagePath, 'http') === 0 || strpos($imagePath, '/storage/') === 0) {
                        $displayImage = $imagePath;
                    } else {
                        $displayImage = Storage::url($imagePath);
                    }
                } else {
                    $displayImage = asset('images/default_item.png');
                }

                $convUnread = (int) ($conversationUnreadMap[$conv->id] ?? $itemUnreadMap[$itemId] ?? 0);

                $viewItems->push((object)[
                    'id' => $conv->id,
                    'is_conversation' => true,
                    'link' => url('/conversations/' . $conv->id),
                    'unread' => $convUnread,
                    'displayImage' => $displayImage,
                    'name' => optional($theItem)->name ?? '（商品情報がありません）',
                    'is_sold' => (bool) optional($theItem)->is_sold,
                ]);
            } else {
                $theItem = $raw;
                $itemId = $theItem->id;

                $imagePath = $theItem->image;
                if (! empty($imagePath)) {
                    if (strpos($imagePath, 'http') === 0 || strpos($imagePath, '/storage/') === 0) {
                        $displayImage = $imagePath;
                    } else {
                        $displayImage = Storage::url($imagePath);
                    }
                } else {
                    $displayImage = asset('images/default_item.png');
                }

                $itemUnread = (int) ($itemUnreadMap[$itemId] ?? ($theItem->unread_messages_count ?? 0));

                $viewItems->push((object)[
                    'id' => $theItem->id,
                    'is_conversation' => false,
                    'link' => url('/item/' . $theItem->id),
                    'unread' => $itemUnread,
                    'displayImage' => $displayImage,
                    'name' => $theItem->name,
                    'is_sold' => (bool) ($theItem->is_sold ?? false),
                ]);
            }
        }

        //
        // 合計未読
        //
        $totalUnread = array_sum($conversationUnreadMap);
        if (empty($totalUnread)) {
            $totalUnread = array_sum($itemUnreadMap);
        }
        $totalUnread = (int) $totalUnread;

        // 評価平均
        $ratingAverage = null;
        try {
            if (class_exists(\App\Models\Rating::class) && schema_has_columns('ratings', ['rated_user_id','score'])) {
                $ratingAverage = \App\Models\Rating::where('rated_user_id', $user->id)->avg('score');
            } elseif (method_exists($user, 'ratingsReceived')) {
                $ratingAverage = $user->ratingsReceived()->avg('score');
            }
        } catch (\Throwable $e) {
            $ratingAverage = null;
        }

        return view('profile', [
            'profile' => $profile,
            'viewItems' => $viewItems,
            'tab' => $tab,
            'totalUnread' => $totalUnread,
            'ratingAverage' => $ratingAverage,
        ]);
    }

    public function edit(Request $request)
    {
        $user = auth()->user();
        $profile = $user->profile;

        if ($request->session()->pull('just_registered') || ! $profile) {
            $profile = new Profile();
        }

        return view('edit', compact('profile'));
    }

    public function update(ProfileRequest $request)
    {
        $user = auth()->user();
        $validated = $request->validated();

        if ($request->hasFile('profile_image')) {
            $dir = 'images';
            $file_name = $request->file('profile_image')->hashName();
            $request->file('profile_image')->storeAs('public/' . $dir, $file_name);
            $validated['image'] = $dir . '/' . $file_name;
        }

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return redirect('/')->with('status', 'プロフィールを保存しました');
    }
}
