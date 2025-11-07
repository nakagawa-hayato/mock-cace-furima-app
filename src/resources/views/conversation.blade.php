{{-- resources/views/conversation.blade.php --}}
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/conversation.css') }}">
@endsection

@section('content')
<div class="conversation-form">
    {{-- サイドバー："その他の取引" --}}
    <aside class="conversation-sidebar">
        <h3 class="conversation-sidebar__heading">その他の取引</h3>
        <ul class="conversation-list">
            @if(!empty($sidebar) && $sidebar->count() > 0)
                @foreach($sidebar as $conv)
                    <li class="conversation-list__item {{ isset($conversation) && $conv->id === $conversation->id ? 'active' : '' }}">
                        <a href="{{ route('conversations.show', ['conversation' => $conv->id]) }}" class="conversation-link">
                            <div class="conv-item-block">
                                <div class="conv-item-name-large">{{ optional($conv->item)->name ?? '（商品名なし）' }}</div>
                                @if(!empty($conv->unread_count) && $conv->unread_count > 0)
                                    <span class="badge">{{ $conv->unread_count }}</span>
                                @endif
                            </div>
                        </a>
                    </li>
                @endforeach
            @endif
        </ul>
    </aside>

    {{-- メインチャットエリア --}}
    <main class="chat-area">
        <div class="chat-header">
            @php
                $user = auth()->user();
                $partner = null;
                if (isset($conversation)) {
                    $partner = ($conversation->seller_id === ($user->id ?? null)) ? $conversation->buyer : $conversation->seller;
                }
            @endphp

            <div class="chat-header__content">
                {{-- 左側：プロフィール画像 + タイトル --}}
                <div class="chat-meta">
                    <div class="profile-thumb">
                        <img src="{{ \Storage::url(optional($partner->profile)->image ?? 'images/default_profile.png') }}" alt="相手プロフィール">
                    </div>
                    <div class="chat-title">
                        「{{ optional($partner)->name ?? '相手' }}」さんとの取引画面
                    </div>
                </div>

                {{-- 右側：取引完了ボタン（購入者のみ） --}}
                @if(isset($conversation) && auth()->check() && $conversation->buyer_id === auth()->id() && (!isset($conversation->is_completed) || !$conversation->is_completed))
                    <button type="button" id="openCompleteModal" class="btn-complete">取引を完了する</button>
                @endif
            </div>
        </div>

        <div class="item-info">
            <div class="item-brief">
                <img src="{{ \Storage::url(optional($conversation->item)->image ?? 'images/default_item.png') }}" alt="商品画像" class="item-thumb">
                <div class="item-info__content">
                    <div class="item-name">{{ optional($conversation->item)->name ?? '商品名' }}</div>
                    <div class="item-price">¥{{ number_format(optional($conversation->item)->price ?? 0) }}</div>
                </div>
            </div>
        </div>

        {{-- メッセージ一覧 --}}
        <section id="messagesWrapper" class="messages-wrapper">
            @if(!empty($messages) && $messages->count() > 0)
                @foreach($messages as $message)
                    @php
                        $isMine = $message->user_id === auth()->id();
                        $sender = $message->user;
                    @endphp

                    <div class="message-row {{ $isMine ? 'mine' : 'theirs' }}" data-message-id="{{ $message->id }}">
                        <div class="message-container">
                            {{-- ヘッダー --}}
                            <div class="message-header {{ $isMine ? 'message-header--mine' : 'message-header--theirs' }}">
                                @if(!$isMine)
                                    <div class="message-avatar">
                                        <img src="{{ \Storage::url(optional($sender->profile)->image ?? 'images/default_profile.png') }}" alt="{{ $sender->name }}">
                                    </div>
                                    <div class="message-name">{{ $sender->name }}</div>
                                @else
                                    <div class="message-name">{{ $sender->name }}</div>
                                    <div class="message-avatar">
                                        <img src="{{ \Storage::url(optional($sender->profile)->image ?? 'images/default_profile.png') }}" alt="{{ $sender->name }}">
                                    </div>
                                @endif
                            </div>

                            {{-- 本文・画像 --}}
                            <div class="message-content {{ $isMine ? 'message-content--mine' : 'message-content--theirs' }}">
                                @if($message->body)
                                    <div class="message-text">{{ e($message->body) }}</div>
                                @endif

                                @if($message->image)
                                    <div class="message-image">
                                        <img src="{{ \Storage::url($message->image) }}" alt="添付画像">
                                    </div>
                                @endif
                            </div>

                            {{-- 自分のアクション --}}
                            @if($isMine)
                                <div class="message-actions-row">
                                    <button type="button" class="link-edit" data-message-id="{{ $message->id }}">編集</button>

                                    <form method="POST" action="{{ route('messages.destroy', ['message' => $message->id]) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="link-delete" type="submit" onclick="return confirm('本当に削除しますか？')">削除</button>
                                    </form>
                                </div>

                                {{-- 編集フォーム（hidden） --}}
                                <form action="{{ route('messages.update', ['message' => $message->id]) }}" method="POST" class="edit-form" data-message-id="{{ $message->id }}" style="display:none;">
                                    @csrf
                                    @method('PUT')
                                    <textarea name="body" maxlength="400" class="edit-body">{{ old('body', $message->body) }}</textarea>
                                    <div class="edit-actions">
                                        <button type="button" class="btn-cancel-edit" data-message-id="{{ $message->id }}">キャンセル</button>
                                        <button type="submit" class="btn-save-edit">保存</button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </section>

        {{-- メッセージ送信フォーム（フッター） --}}
        <div class="chat-footer">
            <form id="messageForm" action="{{ route('messages.store', ['conversation' => $conversation->id]) }}" method="POST" enctype="multipart/form-data" class="send-form" novalidate>
                @csrf

                {{-- Blade 側での個別フィールドエラー（保険として残す） --}}
                @error('body')
                    <p class="form-error-inline" data-field="body">{{ $message }}</p>
                @enderror

                @error('image')
                    <p class="form-error-inline" data-field="image">{{ $message }}</p>
                @enderror

                <div class="input-row">
                    <textarea name="body" id="messageBody" placeholder="取引メッセージを記入してください" maxlength="400" required>{{ old('body', '') }}</textarea>

                    <div class="controls">
                        <label for="messageImage" class="file-label" title="画像を追加">画像を追加</label>
                        <input type="file" name="image" id="messageImage" style="display:none;" />

                        <button type="submit" class="btn-send" aria-label="送信">
                            <img src="{{ asset('storage/images/sent.jpg') }}" alt="送信">
                        </button>
                    </div>
                </div>

                <div id="imagePreview" class="image-preview" style="display:none;">
                    <img id="imagePreviewImg" src="" alt="選択画像プレビュー">
                    <button type="button" id="clearImage" class="clear-image-btn">✕</button>
                </div>
            </form>
        </div>
    </main>
</div>

{{-- 完了モーダル --}}
<div id="completeModal" class="modal" style="display:none;">
    <div class="modal-inner">
        <div class="modal-block">
            <h3>取引が完了しました。</h3>
            <div class="divider"></div>
        </div>

        <div class="modal-block">
            <p class="modal-block__message">今回の取引相手はどうでしたか？</p>
            <div class="rating-stars-input" id="ratingStars" role="radiogroup" aria-label="評価">
                @for ($i = 1; $i <= 5; $i++)
                    <button type="button" class="star-btn" data-value="{{ $i }}" aria-pressed="false" aria-label="{{ $i }}点">★</button>
                @endfor
            </div>
            <input type="hidden" id="ratingScore" value="">
            <input type="hidden" id="rated_user_id" value="{{ ($conversation->seller_id === auth()->id()) ? $conversation->buyer_id : $conversation->seller_id }}">
            <input type="hidden" id="conversation_id" value="{{ $conversation->id }}">
            <div class="divider"></div>
        </div>

        <div class="modal-block modal-actions">
            <button type="button" id="submitRating" class="btn-complete">送信する</button>
        </div>
    </div>
</div>
@endsection {{-- end content --}}

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.getElementById('messagesWrapper');
    const imageInput = document.getElementById('messageImage');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('imagePreviewImg');
    const clearBtn = document.getElementById('clearImage');

    const openBtn = document.getElementById('openCompleteModal');
    const modal = document.getElementById('completeModal');
    const stars = Array.from(document.querySelectorAll('.star-btn'));
    const scoreInput = document.getElementById('ratingScore');
    const ratedUserInput = document.getElementById('rated_user_id');
    const submitBtn = document.getElementById('submitRating');

    if (wrapper) {
        wrapper.scrollTop = wrapper.scrollHeight;
    }

    /* -------------------------
       サーバ側エラーを Blade から受け取る
       Object: { fieldName: [msg1, msg2, ...], ... }
       ------------------------- */
    window.serverErrors = {!! json_encode($errors->messages(), JSON_UNESCAPED_UNICODE) !!} || {};

    // helper: 表示用のエラー要素を作る（field に紐づく）
    function showFormError(field, message) {
        // field に対応する既存要素があれば更新
        let existing = document.querySelector('.form-error-inline[data-field="' + field + '"]');
        if (!existing) {
            existing = document.createElement('div');
            existing.className = 'form-error-inline';
            existing.setAttribute('data-field', field);
            existing.setAttribute('role', 'alert');
            // 挿入位置：フォーム内の .input-row の前（入力欄の上）
            const form = document.getElementById('messageForm');
            const inputRow = form ? form.querySelector('.input-row') : null;
            if (form && inputRow) {
                form.insertBefore(existing, inputRow);
            } else if (form) {
                form.insertBefore(existing, form.firstChild);
            } else {
                // 最終手段：body の先頭
                document.body.insertBefore(existing, document.body.firstChild);
            }
        }
        existing.textContent = message;
    }

    function clearFormError(field) {
        if (field) {
            const el = document.querySelector('.form-error-inline[data-field="' + field + '"]');
            if (el) el.remove();
            return;
        }
        // 全削除
        document.querySelectorAll('.form-error-inline').forEach(el => el.remove());
    }

    // 初期ロード時にサーバエラーがあれば優先表示
    (function applyServerErrors() {
        try {
            // 優先順: body -> image -> first available
            if (window.serverErrors && typeof window.serverErrors === 'object') {
                if (Array.isArray(window.serverErrors.body) && window.serverErrors.body.length > 0) {
                    showFormError('body', window.serverErrors.body[0]);
                    return;
                }
                if (Array.isArray(window.serverErrors.image) && window.serverErrors.image.length > 0) {
                    showFormError('image', window.serverErrors.image[0]);
                    return;
                }
                // その他があれば最初のフィールドの最初のメッセージを表示
                for (const f in window.serverErrors) {
                    if (Array.isArray(window.serverErrors[f]) && window.serverErrors[f].length > 0) {
                        showFormError(f, window.serverErrors[f][0]);
                        return;
                    }
                }
            }
        } catch (e) {
            // ignore
            console.warn('applyServerErrors failed', e);
        }
    })();

    /* -------------------------
       画像 input の change ハンドラ（クライアント検証 + プレビュー）
       - accept 属性を外しているため利用者は任意のファイルを選べます。
       - JS で jpg/png（image/jpeg, image/png）以外は弾き、エラー表示します。
       ------------------------- */
    if (imageInput) {
        imageInput.addEventListener('change', function (e) {
            const file = e.target.files && e.target.files[0];

            // まず既存の image エラーを消す
            clearFormError('image');

            if (!file) {
                if (imagePreview) imagePreview.style.display = 'none';
                return;
            }

            // クライアント側検証ルール（変更可）
            const allowed = ['image/png', 'image/jpeg'];
            const maxBytes = 2 * 1024 * 1024; // 2MB 上限（必要に応じて調整）

            // MIME タイプがない場合（古いブラウザや一部ファイル）は拡張子で判定（後方互換）
            let fileType = file.type && file.type.toLowerCase();
            if (!fileType && file.name) {
                const ext = file.name.split('.').pop().toLowerCase();
                if (ext === 'jpg' || ext === 'jpeg') fileType = 'image/jpeg';
                else if (ext === 'png') fileType = 'image/png';
            }

            if (!allowed.includes(fileType)) {
                showFormError('image', '画像は .png または .jpg/.jpeg のみ対応しています。');
                imageInput.value = '';
                if (imagePreview) imagePreview.style.display = 'none';
                return;
            }

            if (file.size && file.size > maxBytes) {
                showFormError('image', '画像は2MB以下でアップロードしてください。');
                imageInput.value = '';
                if (imagePreview) imagePreview.style.display = 'none';
                return;
            }

            // プレビュー表示
            const reader = new FileReader();
            reader.onload = function (ev) {
                if (previewImg) previewImg.src = ev.target.result;
                if (imagePreview) imagePreview.style.display = 'flex';
                // 成功したら該当エラーを消す
                clearFormError('image');
            };
            reader.readAsDataURL(file);
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            if (imageInput) imageInput.value = '';
            if (previewImg) previewImg.src = '';
            if (imagePreview) imagePreview.style.display = 'none';
            clearFormError('image');
        });
    }

    /* -------------------------
       星評価モーダル関連（既存）
       ------------------------- */
    function setStars(n) {
        const num = parseInt(n || 0, 10);
        stars.forEach(s => {
            const val = parseInt(s.dataset.value || '0', 10);
            if (val <= num) {
                s.classList.add('filled');
                s.setAttribute('aria-pressed', 'true');
            } else {
                s.classList.remove('filled');
                s.setAttribute('aria-pressed', 'false');
            }
            s.textContent = '★';
        });
        if (scoreInput) scoreInput.value = num > 0 ? String(num) : '';
    }

    setStars(0);

    stars.forEach(s => {
        s.tabIndex = 0;
        s.addEventListener('click', function () {
            const n = parseInt(this.dataset.value || '0', 10);
            setStars(n);
        });
        s.addEventListener('keydown', function (ev) {
            if (ev.key === 'Enter' || ev.key === ' ') {
                ev.preventDefault();
                const n = parseInt(this.dataset.value || '0', 10);
                setStars(n);
            } else if (ev.key === 'ArrowLeft' || ev.key === 'ArrowUp') {
                ev.preventDefault();
                const cur = parseInt(scoreInput.value || '0', 10) || 0;
                setStars(Math.max(1, cur - 1));
            } else if (ev.key === 'ArrowRight' || ev.key === 'ArrowDown') {
                ev.preventDefault();
                const cur = parseInt(scoreInput.value || '0', 10) || 0;
                setStars(Math.min(5, cur + 1));
            }
        });
    });

    /* -------------------------
       完了・評価送信（既存 fetch 呼び出しをそのまま使用）
       ------------------------- */
    if (openBtn) {
        openBtn.addEventListener('click', function () {
            if (openBtn.disabled) return;
            openBtn.disabled = true;

            fetch("{{ route('conversations.complete', ['conversation' => $conversation->id]) }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(async (res) => {
                const text = await res.text();
                let json = null;
                try { json = JSON.parse(text); } catch(e) { /* ignore */ }

                if (res.ok) {
                    if (modal) modal.style.display = 'flex';
                } else {
                    const msg = (json && json.message) ? json.message : ('取引完了に失敗しました: ' + res.status);
                    alert(msg);
                }
            })
            .catch(err => {
                console.error(err);
                alert('通信エラーが発生しました。');
            })
            .finally(() => {
                openBtn.disabled = false;
            });
        });
    }

    if (submitBtn) {
        submitBtn.addEventListener('click', function () {
            if (submitBtn.disabled) return;
            const score = parseInt(scoreInput.value || '0', 10);
            if (!score || score < 1 || score > 5) {
                alert('星で評価してください（1〜5）');
                return;
            }

            submitBtn.disabled = true;

            const fd = new FormData();
            fd.append('score', score);
            fd.append('rated_user_id', ratedUserInput ? ratedUserInput.value : '');
            fd.append('item_id', '{{ $conversation->item->id }}');

            const convId = document.getElementById('conversation_id') ? document.getElementById('conversation_id').value : '';
            if (convId) fd.append('conversation_id', convId);

            fetch("{{ route('ratings.store', ['item' => $conversation->item->id]) }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: fd,
                credentials: 'same-origin'
            })
            .then(async (res) => {
                let data = null;
                try { data = await res.json(); } catch (e) { /* ignore */ }

                if (res.ok) {
                    if (modal) modal.style.display = 'none';

                    try {
                        if (convId) {
                            const selector = `[data-conversation-id="${convId}"]`;
                            document.querySelectorAll(selector).forEach(el => el.remove());
                        }
                    } catch (e) {
                        console.warn('DOM remove failed', e);
                    }

                    const redirectUrl = (data && data.redirect) ? data.redirect : null;
                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    } else {
                        window.location.reload();
                    }
                } else if (res.status === 422) {
                    const messages = [];
                    if (data && data.errors) {
                        for (const k in data.errors) {
                            if (Array.isArray(data.errors[k])) messages.push(...data.errors[k]);
                            else messages.push(String(data.errors[k]));
                        }
                    } else if (data && data.message) {
                        messages.push(data.message);
                    }
                    alert('評価送信に失敗しました:\n' + (messages.length ? messages.join('\n') : '入力を確認してください'));
                } else {
                    const msg = (data && data.message) ? data.message : ('評価送信に失敗しました（ステータス: ' + res.status + '）');
                    alert(msg);
                }
            })
            .catch((err) => {
                console.error(err);
                alert('通信エラーが発生しました。');
            })
            .finally(() => {
                submitBtn.disabled = false;
            });
        });
    }

    /* 編集行の toggle / cancel */
    document.querySelectorAll('.link-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.messageId;
            const form = document.querySelector(`.edit-form[data-message-id="${id}"]`);
            if (form) form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
        });
    });

    document.querySelectorAll('.btn-cancel-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.messageId;
            const form = document.querySelector(`.edit-form[data-message-id="${id}"]`);
            if (form) form.style.display = 'none';
        });
    });

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }
});
</script>

{{-- Blade 側で自動オープンする判定（明示的チェック） --}}
@php
    $isSeller = auth()->check() && auth()->id() === ($conversation->seller_id ?? null);
    $isCompleted = isset($conversation->is_completed) ? (bool) $conversation->is_completed : false;
    $isRated = isset($conversation->is_rated) ? (bool) $conversation->is_rated : false;
@endphp

@if($isSeller && $isCompleted && ! $isRated)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('completeModal');
    if (modal) modal.style.display = 'flex';
});
</script>
@endif

@endsection
