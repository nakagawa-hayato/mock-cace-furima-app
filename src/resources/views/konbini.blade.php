@extends('layouts.app')

@section('content')
<div class="konbini-payment">
    <h2>コンビニ支払い情報</h2>
    <p><strong>商品名：</strong> {{ $item->name }}</p>
    <p><strong>金額：</strong> ¥{{ number_format($item->price) }}</p>

    <p>コンビニ支払いのための支払い番号は、StripeのAPIやWebhooksを通じて提供されます。</p>

    {{-- ここに支払い案内が表示される前提（開発中のダミー） --}}
    <p style="color: red;">※実際の支払い番号やバーコードはWebhookで処理するか、Stripe Dashboardで確認してください。</p>

    <a href="/" class="btn btn-primary">トップページへ戻る</a>
</div>
@endsection