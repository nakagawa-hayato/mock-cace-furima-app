@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sell.css')}}">
@endsection

@section('content')
<div class="sell-form">
    <h2 class="sell-form__heading content__heading">商品の出品</h2>
    <div class="sell-form__inner">
        <form class="sell-form__form" action="/sell" method="POST" enctype="multipart/form-data">
        @csrf
            <div class="sell-form__contents">
                <label class="sell-form__label">商品画像</label>
                <div class="sell-form__image-contents">
                    <div class="sell-form__image">
                        <input type="file" name="item_image" id="item_image" >
                        @if (!empty($item->image))
                            <img class="sell-image" src="{{ asset('storage/' . $item->image) }}" alt="商品画像">
                        @else
                            <div class="sell-form__image-select">
                                <label for="item_image">画像を選択する</label>
                            </div>
                        @endif
                    </div>
                </div>
                @error('image')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="sell-form__contents">
                <label class="sell-form__labels">商品の詳細</label>
                <div class="sell-form__content">
                    <label class="sell-form__label">カテゴリー</label>
                    <div class="sell-form__categories">
                        @foreach($categories as $category)
                        <div class="sell-form__category-item">
                            <input type="checkbox"
                                id="category_{{ $category->id }}"
                                name="category[]"
                                value="{{ $category->id }}"
                                {{ in_array($category->id, old('category', [])) ? 'checked' : '' }}>
                            <label for="category_{{ $category->id }}">{{ $category->name }}</label>
                        </div>
                        @endforeach
                    </div>
                    @error('category')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sell-form__content">
                    <label class="sell-form__label">商品の状態</label>
                    <div class="sell-form__select">
                        <select class="sell-form__select-condition" name="condition_id" id="condition_id">
                            <option disabled selected hidden>選択してください</option>
                            @foreach($conditions as $condition)
                                <option value="{{ $condition->id }}"
                                    {{ old('condition_id') == $condition->id ? 'selected' : '' }}>
                                {{ $condition->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('condition')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="sell-form__contents">
                <label class="sell-form__labels">商品名と説明</label>
                <div class="sell-form__content">
                    <label class="sell-form__label" for="name">商品名</label>
                    <input class="sell-form__input" type="text" name="name" id="name">
                    @error('name')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sell-form__content">
                    <label class="sell-form__label" for="bland">ブランド名</label>
                    <input class="sell-form__input" type="text" name="bland" id="bland">
                    @error('bland')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sell-form__content">
                    <label class="sell-form__label" for="description">商品説明</label>
                    <textarea cols="30" rows="5" name="description" id="description" class="sell-form__input-textarea"></textarea>
                    @error('description')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sell-form__content">
                    <label class="sell-form__label" for="price">販売価格</label>
                    <input class="sell-form__input" type="text" placeholder="¥" name="price" id="price">
                    @error('price')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <button type="submit" class="btn button-sell">出品する</button>
        </form>
    </div>
</div>
@endsection