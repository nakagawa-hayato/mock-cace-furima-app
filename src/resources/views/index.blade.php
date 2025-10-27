@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css')}}">
@endsection

@section('content')
<div class="index-form">
    <div class="tab-contents">
        <a href="/" class="recommendation {{ request()->input('tab') !== 'mylist' ? 'active-tab' : '' }}">おすすめ</a>
        <a href="/?tab=mylist&keyword={{ request('keyword') }}" class="my-list {{ request()->input('tab') === 'mylist' ? 'active-tab' : '' }}">マイリスト</a>
    </div>
    <div class="contents">
        <p class="message">{{session('message')}}</p>
        <div class="item-contents">
            @foreach ($items as $item)
                <div class="item-content">
                    <a href="/item/{{ $item->id }}" class="item-link"></a>
                    <img src="{{ \Storage::url($item->image) }}"  alt="商品画像" class="item-img" />
                    <p class="item-name">{{$item->name}}</p>

                    {{-- 売れていれば「SOLD」表示 --}}
                    @if ($item->is_sold)
                        <span class="sold-label">SOLD</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
