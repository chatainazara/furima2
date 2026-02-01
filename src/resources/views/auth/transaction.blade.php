<!DOCTYPE html>
<html lang="ja">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Furima</title>
        <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
        <link rel="stylesheet" href="{{ asset('css/common.css') }}">
        <!-- webフォントの追加 -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
        <!-- webフォントの追加終わり -->
        <link rel="stylesheet" href="{{ asset('css/transaction.css') }}">
    </head>

    <body>

        <header class="header">
            <div class="header__inner">
                <div class="header-utilities">
                    <a class="header__link" href="/">
                        <img class="header__logo" src="{{asset('img/logo.svg')}}" alt="ロゴ">
                    </a>
                </div>
            </div>
        </header>

        <div class="content">
            <div class="box1">
                <div>その他の取引</div>
            </div>
            <!-- 商品画像 -->
            <div class="content-inner">
                <img class="content__img" src="{{asset($item['pict_url'])}}" alt="" style="width:100%;"/>
            </div>

            <!-- その他の情報 -->
            <div class="content-inner">
                <div class="content__title">
                    <h1 class="content__title-text">{{$item['name']}}</h1>
                </div>
                <div class="content__brand">
                    <p class="content__brand-name">{{$item['brand_name']}}</p>
                </div>
                <div class="content__price">
                    <p class="subscript">¥</p>
                    <p class="main-text"><?php echo number_format($item['price'],0); ?></p>
                    <p class="subscript">（税込）</p>
                </div>

                <div class="content__sub-title">
                    <h2 class="content__sub-title--text">商品説明</h2>
                </div>
                <div class="content__detail">
                    <p class="content__detail--text">{{$item['detail']}}</p>
                </div>
                <div class="content__sub-title">
                    <h2 class="content__sub-title--text">商品の情報</h2>
                </div>

                <div class="content__info">
                    <div class="content__info--title">
                        商品の状態
                    </div>
                    <div  class="content__info--item-condition">
                        {{$item['condition']}}
                    </div>
                </div>

            </div>
        </div>
    </body>
</html>