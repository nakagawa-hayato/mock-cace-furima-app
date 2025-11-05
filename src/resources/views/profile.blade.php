{{-- resources/views/profile.blade.php --}}
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/index.css')  }}" >
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('content')
<div class="profile-form">
    <div class="profile-info">
        <div class="profile-image__area">
            <div class="profile-image__circle">
                @if (!empty($profile->image))
                    <img src="{{ \Storage::url($profile->image) }}" alt="プロフィール画像" class="profile-image">
                @else
                    <div class="placeholder-circle"></div>
                @endif
            </div>

            <div class="profile-name">
                <h2>{{ $profile->display_name ?? 'ユーザー' }}</h2>

                @php
                    $ratingAverage = $ratingAverage ?? null;
                    $rounded = is_null($ratingAverage) ? null : (int) round($ratingAverage);
                    if (!is_null($rounded)) {
                        $rounded = max(0, min(5, $rounded));
                    }
                @endphp

                @if (!is_null($rounded))
                    <div class="rating-stars" aria-label="評価：{{ number_format($ratingAverage, 1) }} / 5">
                        @for ($i = 1; $i <= 5; $i++)
                            @if ($i <= $rounded)
                                <span class="star filled">★</span>
                            @else
                                <span class="star">★</span>
                            @endif
                        @endfor
                    </div>
                @endif
            </div>
        </div>

        <div class="profile-info__link">
            <a href="/mypage/profile" class="btn-edit">プロフィールを編集</a>
        </div>
    </div>

    <div class="tab-contents">
        <a href="{{ url('/mypage?tab=sell') }}" class="my-page_sell {{ ($tab ?? request()->input('tab')) === 'sell' ? 'active-tab' : '' }}">
            出品した商品
        </a>

        <a href="{{ url('/mypage?tab=buy') }}" class="my-page_buy {{ ($tab ?? request()->input('tab')) === 'buy' ? 'active-tab' : '' }}">
            購入した商品
        </a>

        <a href="{{ url('/mypage?tab=transactions') }}" class="my-page_transactions {{ ($tab ?? request()->input('tab')) === 'transactions' ? 'active-tab' : '' }}">
            取引中の商品
            @if (!empty($totalUnread) && $totalUnread > 0)
                <span class="tab-badge">{{ $totalUnread }}</span>
            @endif
        </a>
    </div>

    <div class="contents">
        <p class="message">{{ session('message') }}</p>

        <div class="item-contents">
            @forelse ($viewItems as $it)
            <div class="item-content" @if($it->is_conversation) data-conversation-id="{{ $it->id }}" @endif>
                <a href="{{ $it->link }}" class="item-link"></a>

                <div class="item-img-wrap">
                    <img src="{{ $it->displayImage }}" alt="商品画像" class="item-img" />

                    @if (!empty($it->unread) && $it->unread > 0 && ($tab ?? request()->input('tab', 'sell')) === 'transactions')
                        @php $displayCount = $it->unread > 99 ? '99+' : $it->unread; @endphp

                        @if ($it->is_conversation)
                            <span class="message-badge" aria-label="未読メッセージ {{ $it->unread }} 件">{{ $displayCount }}</span>
                        @else
                            <span class="item-badge" aria-label="未読 {{ $it->unread }} 件">{{ $displayCount }}</span>
                        @endif
                    @endif
                </div>

                <p class="item-name">{{ $it->name }}</p>

                @if (!empty($it->is_sold))
                    <span class="sold-label">SOLD</span>
                @endif
            </div>
            @empty
                <p class="no-items">表示する商品がありません。</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
