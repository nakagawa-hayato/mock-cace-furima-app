@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('content')
<div class="profile-form">
    <div class="profile-info">
        <div class="profile-image__area">
            <div class="profile-image__circle">
                @if ($profile->image)
                    <img src="{{ asset('storage/' . $profile->image) }}" alt="プロフィール画像" class="profile-image">
                @else
                    <div class="placeholder-circle"></div>
                @endif
            </div>
            <div class="profile-name">
                <h2>{{ $profile->display_name }}</h2>
            </div>
        </div>
        <div class="profile-info__link">
            <a href="/mypage/profile" class="btn-edit">プロフィールを編集</a>
        </div>
    </div>

    <div class="tab-contents">
        <a href="/mypage?tab=sell"
        class="my-page_sell {{ request()->input('tab') === 'sell' ? 'active-tab' : '' }}">
        出品した商品
        </a>
        <a href="/mypage?tab=buy"
        class="my-page_buy {{ request()->input('tab') === 'buy' ? 'active-tab' : '' }}">
        購入した商品
        </a>
    </div>

    <div class="contents">
        <p class="message">{{session('message')}}</p>
        <div class="item-contents">
            @foreach ($items as $item)
                <div class="item-content">
                    <a href="/item/{{ $item->id }}" class="item-link"></a>
                    <img src="{{ asset('storage/' . $item->image) }}" alt="商品画像" class="item-img" />
                    <p class="item-name">{{$item->name}}</p>

                    {{-- 売れていれば「SOLD」表示 --}}
                    @if ($item->is_sold)
                        <span class="sold-label">SOLD</span>
                    @endif
                </div>
            @endforeach
        </div>
</div>

@endsection
