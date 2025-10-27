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

{{-- edit.blade.php の末尾に追加（@endsection の直前） --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
  const fileInput = document.getElementById('profile_image');
  const container = document.querySelector('.edit-form__image');
  const triggerLabel = document.querySelector('.edit-form__image-select label') || document.querySelector('.edit-form__image-select');

  if (!fileInput || !container) return;

  // クリックでファイル選択を開く（プレビューまたはラベルをクリック）
  container.style.cursor = 'pointer';
  container.addEventListener('click', () => fileInput.click());
  if (triggerLabel) triggerLabel.addEventListener('click', () => fileInput.click());

  fileInput.addEventListener('change', handleImageSelect);

  function handleImageSelect(e) {
    const file = e.target.files && e.target.files[0];
    if (!file) return;

    // MIME とサイズチェック
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    const maxSize = 5 * 1024 * 1024; // 5MB

    if (!allowedTypes.includes(file.type)) {
      alert('画像は JPEG / PNG のみアップロード可能です。');
      fileInput.value = '';
      return;
    }
    if (file.size > maxSize) {
      alert('画像サイズは 5MB 以下にしてください。');
      fileInput.value = '';
      return;
    }

    const reader = new FileReader();
    reader.onload = (ev) => {
      // 既存のプレビュー画像やプレースホルダーを削除
      const existingImg = container.querySelector('img.edit-image');
      const placeholder = container.querySelector('.placeholder-circle');

      if (existingImg) existingImg.remove();
      if (placeholder) placeholder.remove();

      // 画像要素を作って挿入
      const img = document.createElement('img');
      img.src = ev.target.result;
      img.alt = '選択したプロフィール画像プレビュー';
      img.className = 'edit-image';
      img.style.width = '100%';
      img.style.height = '100%';
      img.style.objectFit = 'cover';
      img.style.borderRadius = '50%';
      container.appendChild(img);

      // 画像をクリックすると再選択できるように
      img.addEventListener('click', () => fileInput.click());
    };
    reader.readAsDataURL(file);
  }
});
</script>

@endsection

