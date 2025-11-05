<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>取引完了のお知らせ</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>
  <h2>取引が完了しました</h2>

  <p>
    {{ $rater->name }} さんがあなたの商品
    「{{ $item->name ?? '（商品名不明）' }}」の取引を完了しました。
  </p>

  <hr>

  <p class="muted">このメールは自動送信です。心当たりがない場合はお問い合わせください。</p>
</body>
</html>
