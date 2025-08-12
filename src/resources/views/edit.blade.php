@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/edit.css')}}">
@endsection

@section('content')
<div class="edit-form">
    <h2 class="edit-form__heading content__heading">プロフィール設定
    </h2>
    <div class="edit-form__inner">
        <form class="edit-form__form" action="/mypage/profile" method="POST" enctype="multipart/form-data">
            @method('PATCH')
            @csrf
            <div class="edit-form__image-contents">
                <div class="edit-form__image">
                    <input type="file" name="profile_image" id="profile_image" >
                    @if (!empty($profile->image))
                        <img class="edit-image" src="{{ asset('storage/' . $profile->image) }}" alt="プロフィール画像">
                    @else
                        <div class="placeholder-circle"></div>
                    @endif
                </div>
                <div class="edit-form__image-select">
                    <label for="profile_image">画像を選択する</label>
                </div>
            </div>
            <div class="edit-form__group">
                <label class="edit-form__label" for="display_name">ユーザー名</label>
                <input class="edit-form__input" type="text" name="display_name" id="display_name" value="{{ old('display_name' , $profile->display_name ?? '') }}">
                <p class="edit-form__error-message">
                    @error('display_name')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <div class="edit-form__group">
                <label class="edit-form__label" for="post_code">郵便番号
                </label>
                <input class="edit-form__input" type="text" name="post_code" id="post_code" value="{{ old('post_code', $profile->post_code ?? '') }}">
                <p class="edit-form__error-message">
                    @error('post_code')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <div class="edit-form__group">
                <label class="edit-form__label" for="address">住所</label>
                <input class="edit-form__input" type="text" name="address" id="address" value="{{ old('address', $profile->address ?? '') }}">
                <p class="edit-form__error-message">
                    @error('address')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <div class="edit-form__group">
                <label class="edit-form__label" for="building">建物名</label>
                <input class="edit-form__input" type="text" name="building" id="building" value="{{ old('building', $profile->building ?? '') }}">
                <p class="edit-form__error-message">
                    @error('building')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <input class="edit-form__btn btn" type="submit" value="更新する">
        </form>
    </div>
</div>
@endsection

