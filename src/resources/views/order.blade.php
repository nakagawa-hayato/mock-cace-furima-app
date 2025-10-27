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
                            <img src="{{ \Storage::url($item->image) }}"  alt="商品画像" class="image">
                        </div>
                        <div class="item-info">
                            <div class="item-name">
                                <label>{{ $item->name }}</label>
                            </div>
                            <div class="item-price">
                                <span>¥</span><span class="price">{{ $item->price }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="left-content">
                    <label class="left-content__label">支払い方法</label>
                    <div class="select-wrapper">
                        <select class="select" id="payment" name="payment_method" required>
                            <option value="" disabled selected hidden>選択してください</option>
                            <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>クレジットカード支払い</option>
                            <option value="konbini" {{ old('payment_method') == 'konbini' ? 'selected' : '' }}>コンビニ支払い</option>
                        </select>
                        @error('payment_method')
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
                        <label>〒 
                            <input class="input_destination" name="post_code" 
                                value="{{ old('post_code', $address['post_code'] ?? '') }}" readonly>
                        </label>
                        <br>
                        <input class="input_destination" name="address" 
                            value="{{ old('address', $address['address'] ?? '') }}" readonly>
                        @if (!empty($address['building']))
                            <input class="input_destination" name="building" 
                                value="{{ old('building', $address['building'] ?? '') }}" readonly>
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
                        <p class="confirm-text" id="confirm-payment">
                            {{ old('payment_method') ? (old('payment_method') === 'card' ? 'クレジットカード支払い' : 'コンビニ支払い') : '未選択' }}
                        </p>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">購入する</button>
            </div>
        </div>
    </form>
</div>

<!-- 支払い選択を確認領域に反映する JS -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('payment');
    const confirmEl = document.getElementById('confirm-payment');

    function updateConfirm() {
        const v = select ? select.value : null;
        if (v === 'card') {
            confirmEl.textContent = 'クレジットカード支払い';
        } else if (v === 'konbini') {
            confirmEl.textContent = 'コンビニ支払い';
        } else {
            confirmEl.textContent = '未選択';
        }
    }

    // ページ読み込み時に初期表示を合わせる
    updateConfirm();

    if (select) {
        select.addEventListener('change', updateConfirm);
    }
});
</script>
@endsection
