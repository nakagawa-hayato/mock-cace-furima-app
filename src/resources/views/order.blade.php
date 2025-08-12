@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/order.css')}}">
@endsection

@section('content')
<div class="order-form">
    <form class="order-form__form" action="/purchase/{{ $item->id }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="order-contents">
            <div class="left-contents">
                <div class="left-content">
                    <div class="item-info__area">
                        <div class="item-image">
                            <img src="{{ asset('storage/' . $item->image) }}" alt="商品画像" class="image">
                        </div>
                        <div class="item-info">
                            <div class="item-name">
                                <label>{{ $item->name }}</label>
                            </div>
                            <div class="item-price">
                                <span>¥</span><span class="price">{{ $item->price }}<span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="left-content">
                    <label class="left-content__label">支払い方法</label>
                    <div class="select-wrapper">
                    <select class="select" name="method" >
                        <option value="" disabled selected hidden>選択してください</option>
                        <option value="カード支払い" {{ old('method') == 'カード支払い' ? 'selected' : '' }}>カード支払い</option>
                        <option class="select-option" value="コンビニ支払い" {{ old('method') == 'コンビニ支払い' ? 'selected' : '' }}>コンビニ支払い</option>
                    </select>
                    @error('method')
                        <p class="error">{{ $message }}</p>
                    @enderror
                    </div>
                </div>
                
                <div class="left-content">
                    <div class="left-content__header">
                        <label class="left-content__label">配送先</label>
                        <a href="/purchase/address/{{ $item->id }}" class="btn-secondary">変更する</a>
                    </div>
                    <div class="address">
                    @php
                        $profile = auth()->user()->profile;
                    @endphp

                    @if (session()->has('order_post_code') || session()->has('order_address'))
                        <p>
                        〒{{ session('order_post_code') ?? $profile->post_code }}<br>
                        {{ session('order_address') ?? $profile->address }}
                        {{ session('order_building') ?? $profile->building }}
                        </p>
                    @else
                        {{-- プロフィールからの情報を表示 --}}
                        <p>
                            〒{{ $profile->post_code }}<br>
                            {{ $profile->address }}
                            {{ $profile->building }}
                        </p>
                    @endif
                    </div>
                </div>
            </div>
            
            <div class="right-contents">
                <div class="confirm-contents">
                    <div class="confirm-content">
                        <label class="confirm-label">商品代金</label>
                        <p class="confirm-text">¥{{ number_format($item->price) }}</p>
                    </div>
                    <div class="confirm-content">
                        <label class="confirm-label">支払い方法</label>
                        <p class="confirm-text">コンビニ支払い</p>
                    </div>
                </div>
                    <button type="submit" class="btn btn-primary">購入する</button>
            </div>
        </div>
    </form>
</div>
@endsection