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
                        <input type="file" name="image" id="item_image" >
                        @if (!empty($item->image))
                            <img class="sell-image" src="{{ \Storage::url($item->image) }}" alt="商品画像">
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
                    <label class="sell-form__label" for="brand">ブランド名</label>
                    <input class="sell-form__input" type="text" name="brand" id="brand">
                    @error('brand')
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

{{-- sell.blade.php の末尾に追加（@endsection の直前） --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
  const fileInput = document.getElementById('item_image');
  if (!fileInput) return;

  fileInput.addEventListener('change', handleImageSelect);

  function handleImageSelect(e) {
    const file = e.target.files && e.target.files[0];
    if (!file) return;

    // MIME とサイズチェック（必要ならメッセージをカスタマイズ）
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
      const container = document.querySelector('.sell-form__image');
      if (!container) return;

      // 既存のプレビュー画像や「画像を選択する」ラベルを消す
      const existingImg = container.querySelector('img.sell-image');
      const placeholder = container.querySelector('.sell-form__image-select');
      if (existingImg) existingImg.remove();
      if (placeholder) placeholder.remove();

      // 画像要素を作って挿入
      const img = document.createElement('img');
      img.src = ev.target.result;
      img.alt = '選択した画像プレビュー';
      img.className = 'sell-image';
      // 必要ならスタイルやデータ属性を追加
      container.appendChild(img);
    };
    reader.readAsDataURL(file);
  }
});
</script>

@endsection