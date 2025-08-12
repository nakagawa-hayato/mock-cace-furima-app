<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FurimaApp</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <div class="app">
        <header class="header">
            <div class="header__heading">
                <a href="/" class="logo">
                    <img src="{{ asset('storage/images/logo.svg') }}" alt="タイトル画像" class="img-logo-svg" />
                </a>
            </div>
            @yield('link')

            @if (!Request::is('login') && !Request::is('register'))
                <div class="search-form">
                    <form class="search" action="/" method="GET" >
                        <input type="text" name="keyword" class="keyword" placeholder="なにをお探しですか？" value="{{ request('keyword') }}">

                        @if (request('tab') === 'mylist')
                            <input type="hidden" name="tab" value="mylist">
                        @endif
                    </form>
                </div>

                <div class="header__nav">
                    @auth
                        <div class="header__item">
                            <form action="/logout" method="POST">
                                @csrf
                                <button class="header-nav__button" type="submit">ログアウト</button>
                            </form>
                        </div>
                    @else
                        <div class="header__item">
                            <a href="/login" class="login">ログイン</a>
                        </div>
                    @endauth
                    <div class="header__item">
                        <a href="/mypage" class="mypage">マイページ</a>
                    </div>
                    <div class="header__item">
                        <a href="/sell" class="sell">出品</a>
                    </div>
                </div>
            @endif
        </header>
        <div class="content">
            @yield('content')
        </div>
    </div>
</body>

</html>


