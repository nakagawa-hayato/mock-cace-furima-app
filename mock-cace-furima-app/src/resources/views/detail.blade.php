@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css')}}">
@endsection

@section('content')
<div class="detail-form">
    <div class="detail-contents">
        <div class="left-contents">
            <img src="{{ asset('storage/' . $item->image) }}" alt="商品画像" class="item-img">
        </div>
        <div class="right-contents">
            <div class="right-content">
                <label class="item-name">{{ $item->name }}</label>
                <span class="item-bland">{{ $item->bland }}</span>
                <p><span>¥</span><span class="item-price">{{ number_format($item->price) }}</span><span> (税込)</span></p>
                
                <div class="count-area">
                    <div class="count-box">
                        @auth
                            <form action="/favorite/{{ $item->id }}" method="POST">
                                @csrf
                                @if ($item->isFavoritedBy(auth()->user()))
                                    @method('DELETE')
                                    <button type="submit" class="icon-btn">⭐️</button>
                                @else
                                    <button type="submit" class="icon-btn">☆</button>
                                @endif
                            </form>
                        @else
                            <span class="icon">☆</span>
                        @endauth
                        <span class="count-num">{{ $item->favorites->count() }}</span>
                    </div>
                    <div class="count-box">
                        <span class="icon">💬</span>
                        <span class="count-num">{{ $item->comments->count() }}</span>
                    </div>
                </div>

                @auth
                    <div class="btn link-btn">
                        <a href="/purchase/{{ $item->id }}">購入手続きへ</a>
                    </div>
                @endauth
            </div>

            <div class="right-content">
                <label class="label-subtitle">商品説明</label>
                <p>{{ $item->description }}</p>
            </div>

            <div class="right-content">
                <label class="label-subtitle">商品の情報</label>
                <div class="item-info">
                    <div class="info-contents">
                    <label class="info-label">カテゴリー</label>
                    @foreach ($item->categories as $category)
                        <span class="info-categories">{{ $category->name }}</span>
                    @endforeach
                    </div>
                    <div class="info-contents">
                        <label class="info-label">商品の状態</label>
                        <span>{{ $item->condition->name }}</span>
                    </div>
                </div>
            </div>

            <div class="right-content">
                <label class="comment-label">コメント({{ $item->comments->count() }})</label>
                @foreach ($item->comments as $comment)
                    <div class="comment-area">
                        <div class="user-info">
                            <div class="profile-image__circle">
                                @if ($comment->user->profile->image)
                                    <img src="{{ asset('storage/' . $comment->user->profile->image) }}" alt="プロフィール画像" class="profile-image">
                                @else
                                    <div class="placeholder-circle"></div>
                                @endif
                            </div>
                            <div class="profile-name">
                                <strong>{{ $comment->user->profile->display_name }}</strong>
                            </div>
                        </div>
                    <div class="comment-box">
                        {{ $comment->comment }}
                    </div>
                @endforeach
            </div>
            
            <div class="right-content">
                <label class="comment-input">商品へのコメント</label>
                @auth
                    <form action="/comment" method="POST">
                        @csrf
                        <input type="hidden" name="item_id" value="{{ $item->id }}">
                        <textarea cols="30" rows="5" name="comment" class="comment__input-box">{{ old('comment') }}</textarea>
                        @error('comment')
                        <p class="error">
                            {{ $message }
                        }</p>
                        @enderror
                        <button type="submit" class="btn btn-comment">コメントを送信する</button>
                    </form>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection