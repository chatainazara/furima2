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
    <!-- ヘッダー部分、他画面とは非共有 -->
    <header class="header">
        <div class="header__inner">
            <div class="header-utilities">
                <a class="header__link" href="/">
                    <img class="header__logo" src="{{asset('img/logo.svg')}}" alt="ロゴ">
                </a>
            </div>
        </div>
    </header>
    <!-- ヘッダー部分終了 -->

    @if($position == 'seller')
    <!-- 販売者としての視点 -->
    <div class="content">
        <!-- 左サイド -->
        <aside class="content-left">
            <div class="content-left__title">その他の取引</div>
            <div class="content-left__list">
                @foreach($buys as $buy)
                @php
                $eval = $buy?->evaluation ?? null; // 評価があれば取る
                $isDone = $eval && !is_null($eval->evaluate) && !is_null($eval->evaluated); // 両方埋まってたら完了
                @endphp
                @if($isDone)
                @else
                <form action="/transaction/{{$buy->item->id}}" method="get" class="content-left__item">
                    <button type="submit" class="content-left__btn">{{$buy->item->name}}</button>
                </form>
                @endif
                @endforeach
            </div>
        </aside>
        <!-- 右メイン -->
        <main class="content-right">
            <!-- 上ヘッダー -->
            <section class="content-head">
                <div class="content-head__left">
                    <div class="content-avatar">
                        <img src="{{ asset($transactionItem->user->profile->pict_url ?? 'img/noimage.png') }}" alt="">
                    </div>
                    <h2 class="content-head__title">「{{$transactionItem->user->name}}」さんとの取引画面</h2>
                </div>
            </section>
            <!-- 商品カード -->
            <section class="content-item">
                <div class="content-item__img">
                    <img src="{{ asset($transactionItem->item->pict_url) }}" alt="">
                </div>
                <div class="content-item__info">
                    <div class="content-item__name">{{$transactionItem->item->name}}</div>
                    <div class="content-item__price">¥ {{ number_format($transactionItem->item->price,0) }}</div>
                </div>
            </section>
            <!-- チャット欄 -->
            <section class="content-chat">
                @foreach($chats as $chat)
                @php
                // 自分の発言かどうか（position が seller/buyer で入っている前提）
                $isMine = ($chat->position === $position);
                @endphp
                <div class="content-msg {{ $isMine ? 'content-msg--mine' : 'content-msg--other' }}">
                    <div class="content-msg__avatar">
                        <img
                            src="{{ asset(
                                    $isMine
                                    ? (Auth::user()->profile->pict_url ?? 'img/noimage.png')
                                    : ($position === 'seller'
                                            ? ($chat->buy->user->profile->pict_url ?? 'img/noimage.png')   // 相手=buyer
                                            : ($chat->buy->item->user->profile->pict_url ?? 'img/noimage.png') // 相手=seller
                                        )
                                ) }}"
                            alt="">
                    </div>
                    <div class="content-msg__body">
                        <div class="content-msg__name">
                            {{ $isMine ? Auth::user()->name : ($position==='seller' ? $chat->buy->user->name : $chat->buy->item->user->name) }}
                        </div>
                        {{-- 編集中（自分の発言だけ） --}}
                        @if($isMine && $editId == $chat->id)
                        <form class="content-edit" action="/transaction/update/{{$chat->id}}" method="post">
                            @csrf
                            <textarea name="chat" class="content-edit__textarea">{{$chat->chat}}</textarea>
                            <button type="submit" class="content-edit__save">保存</button>
                        </form>
                        @else
                        <div class="content-msg__bubble">
                            {{$chat->chat}}
                        </div>
                        @endif
                        @if($chat->pict)
                        <div class="content-msg__image">
                            <img src="{{ asset($chat->pict) }}" alt="">
                        </div>
                        @endif
                        {{-- 自分の発言だけ操作を表示 --}}
                        @if($isMine)
                        <div class="content-msg__actions">
                            <form action="/transaction/{{$chat->buy->item->id}}" method="get">
                                <input type="hidden" name="editId" value="{{ $chat->id }}">
                                <button class="content-action" type="submit">編集</button>
                            </form>
                            <form action="/transaction/delete/{{$chat->id}}" method="post">
                                @csrf
                                @method('delete')
                                <button class="content-action content-action--danger" type="submit">削除</button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </section>
            <!-- 入力バー -->
            <section class="content-input">
                @error('chat')
                <div class="error">
                    {{ $message }}
                </div>
                @enderror
                <form class="content-input__form" action="/transaction/store" method="post" enctype="multipart/form-data">
                    @csrf
                    <textarea
                        name="chat"
                        id="chat"
                        class="content-input__textarea"
                        placeholder="取引メッセージを記入してください">{{ old('chat', session('chat_draft')) }}</textarea>
                    <input type="file" name="pict" id="pict" class="hidden" accept="image/png,image/jpeg">
                    <input type="hidden" name="buyId" value="{{$transactionItem->id}}">
                    <input type="hidden" name="position" value="{{$position}}">
                    <label for="pict" class="content-input__add">画像を追加</label>
                    <button type="submit" class="content-input__send" aria-label="送信">
                        <img src="{{asset('img/inputbuttun1.svg')}}" alt="">
                    </button>
                </form>
            </section>
        </main>
    </div>
    <!-- 販売者としての視点終わり -->

    @else
    <!-- 購入者としての視点 -->
    <div class="content">
        <!-- 左サイド -->
        <aside class="content-left">
            <div class="content-left__title">その他の取引</div>
            <div class="content-left__list">
                @foreach($buys as $buy)
                @php
                $eval = $buy?->evaluation ?? null; // 評価があれば取る
                $isDone = $eval && !is_null($eval->evaluate) && !is_null($eval->evaluated); // 両方埋まってたら完了
                @endphp
                @if($isDone)
                @else
                <form action="/transaction/{{$buy->item->id}}" method="get" class="content-left__item">
                    <button type="submit" class="content-left__btn">{{$buy->item->name}}</button>
                </form>
                @endif
                @endforeach
            </div>
        </aside>
        <!-- 右メイン -->
        <main class="content-right">
            <!-- 上ヘッダー -->
            <section class="content-head">
                <div class="content-head__left">
                    <div class="content-avatar">
                        <img src="{{ asset($transactionItem->item->user->profile->pict_url ?? 'img/noimage.png') }}" alt="">
                    </div>
                    <h2 class="content-head__title">「{{$transactionItem->item->user->name}}」さんとの取引画面</h2>
                </div>
                @if($needsEvaluation)
                <form action="">
                    <button type="button" class="content-complete" id="openCompleteModal">取引を完了する</button>
                </form>
                @else
                @endif
            </section>
            <!-- 商品カード -->
            <section class="content-item">
                <div class="content-item__img">
                    <img src="{{ asset($transactionItem->item->pict_url) }}" alt="">
                </div>
                <div class="content-item__info">
                    <div class="content-item__name">{{$transactionItem->item->name}}</div>
                    <div class="content-item__price">¥ {{ number_format($transactionItem->item->price,0) }}</div>
                </div>
            </section>
            <!-- チャット欄 -->
            <section class="content-chat">
                @foreach($chats as $chat)
                @php
                // 自分の発言かどうか
                $isMine = ($chat->position === $position);
                @endphp

                <div class="content-msg {{ $isMine ? 'content-msg--mine' : 'content-msg--other' }}">
                    <div class="content-msg__avatar">
                        <img src="{{ asset($isMine
                                    ? (Auth::user()->profile->pict_url ?? 'img/noimage.png')
                                    : ($position === 'seller'
                                            ? ($chat->buy->user->profile->pict_url ?? 'img/noimage.png')   // 相手=buyer
                                            : ($chat->buy->item->user->profile->pict_url ?? 'img/noimage.png') // 相手=seller
                                        )
                                ) }}"
                            alt="">
                    </div>
                    <div class="content-msg__body">
                        <div class="content-msg__name">
                            {{ $isMine ? Auth::user()->name : ($position==='seller' ? $chat->buy->user->name : $chat->buy->item->user->name) }}
                        </div>
                        {{-- 編集中（自分の発言だけ） --}}
                        @if($isMine && $editId == $chat->id)
                        <form class="content-edit" action="/transaction/update/{{$chat->id}}" method="post">
                            @csrf
                            <textarea name="chat" class="content-edit__textarea">{{$chat->chat}}</textarea>
                            <button type="submit" class="content-edit__save">保存</button>
                        </form>
                        @else
                        <div class="content-msg__bubble">
                            {{$chat->chat}}
                        </div>
                        @endif
                        @if($chat->pict)
                        <div class="content-msg__image">
                            <img src="{{ asset($chat->pict) }}" alt="">
                        </div>
                        @endif
                        {{-- 自分の発言だけ操作を表示 --}}
                        @if($isMine)
                        <div class="content-msg__actions">
                            <form action="/transaction/{{$chat->buy->item->id}}" method="get">
                                <input type="hidden" name="editId" value="{{ $chat->id }}">
                                <button class="content-action" type="submit">編集</button>
                            </form>
                            <form action="/transaction/delete/{{$chat->id}}" method="post">
                                @csrf
                                @method('delete')
                                <button class="content-action content-action--danger" type="submit">削除</button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </section>
            <!-- 入力バー -->
            <section class="content-input">
                @error('chat')
                <div class="error">
                    {{ $message }}
                </div>
                @enderror
                <form class="content-input__form" action="/transaction/store" method="post" enctype="multipart/form-data">
                    @csrf
                    <textarea
                        name="chat"
                        id="chat"
                        class="content-input__textarea"
                        placeholder="取引メッセージを記入してください">{{ old('chat', session('chat_draft')) }}</textarea>
                    <input type="file" name="pict" id="pict" class="hidden" accept="image/png,image/jpeg">
                    <input type="hidden" name="buyId" value="{{$transactionItem->id}}">
                    <input type="hidden" name="position" value="{{$position}}">
                    <label for="pict" class="content-input__add">画像を追加</label>
                    <button type="submit" class="content-input__send" aria-label="送信">
                        <img src="{{asset('img/inputbuttun1.svg')}}" alt="">
                    </button>
                </form>
            </section>
        </main>
    </div>
    <!-- 購入者としての視点で終わり -->
    @endif
    <!-- モーダル -->
    <div class="rate-overlay" id="rateModal" aria-hidden="true">
        <div class="rate-modal" role="dialog" aria-modal="true" aria-labelledby="rateTitle">
            <div class="rate-modal__header">
                <h3 id="rateTitle" class="rate-modal__header--text">取引が完了しました。</h3>
            </div>
            <div class="rate-modal__body">
                <div class="rate-modal__main">
                    <p class="rate-modal__text">今回の取引相手はどうでしたか？</p>
                    @if($position === 'buyer')
                    <form action="/evaluation/store/{{$transactionItem->id}}" method="post" id="rateForm">
                        @elseif($position === 'seller')
                        <form action="/evaluation/update/{{$transactionItem->id}}" method="post" id="rateForm">
                            @endif
                            @csrf
                            {{-- 送信用の値 --}}
                            <input type="hidden" name="rating" id="ratingValue" value="0">
                            <div class="stars" id="stars">
                                <button type="button" class="star" data-value="1" aria-label="1stars">★</button>
                                <button type="button" class="star" data-value="2" aria-label="2stars">★</button>
                                <button type="button" class="star" data-value="3" aria-label="3stars">★</button>
                                <button type="button" class="star" data-value="4" aria-label="4stars">★</button>
                                <button type="button" class="star" data-value="5" aria-label="5stars">★</button>
                            </div>
                </div>
                <div class="rate-modal__footer">
                    <button type="submit" class="rate-submit" id="rateSubmit" disabled>送信する</button>
                </div>
                </form>
            </div>
        </div>
    </div>
    <!-- モーダルウィンドウ終わり -->

    <!-- メッセージの記憶 -->
    <script>
        const chat = document.getElementById('chat');
        const buyId = "{{ $transactionItem->id }}"; // 取引IDごとにキーを変える
        const storageKey = 'chat_draft_' + buyId;
        // 入力するたびに保存
        chat.addEventListener('input', function() {
            sessionStorage.setItem(storageKey, this.value);
        });
        // ページ読み込み時に復元
        window.addEventListener('load', function() {
            const saved = sessionStorage.getItem(storageKey);
            if (saved && !chat.value) {
                chat.value = saved;
            }
        });
        // フォーム送信時に下書きを消す
        chat.form.addEventListener('submit', function() {
            sessionStorage.removeItem(storageKey);
        });
    </script>

    <!-- モーダルウィンドウ -->
    <script>
        // モーダル自体の動作
        const openBtn = document.getElementById('openCompleteModal');
        const modal = document.getElementById('rateModal');

        function openModal() {
            modal.style.display = 'flex';
        }

        function closeModal() {
            modal.style.display = 'none';
        }
        if (openBtn) {
            openBtn.addEventListener('click', (event) => {
                openModal();
            });
        }
        modal.addEventListener('click', (event) => { //モーダル外のクリックで閉じる
            if (event.target === modal) {
                closeModal();
            }
        });

        // ここから下は星評価の部分
        const starsWrap = document.getElementById('stars');
        const ratingValue = document.getElementById('ratingValue');
        const rateSubmit = document.getElementById('rateSubmit');
        const stars = starsWrap.querySelectorAll('.star');
        // 星の色塗り部分
        function paintStars(value) {
            stars.forEach((btn) => {
                const evaluate = Number(btn.dataset.value); //dataset.valueはdata-valueの中身を取得
                if (evaluate <= value) {
                    btn.classList.add('is-active');
                } else {
                    btn.classList.remove('is-active');
                }
            });
        }
        // クリックした星のvalueを取得
        stars.forEach((btn) => {
            btn.addEventListener('click', () => { //各星が値を持つので()内にeventは不要
                const selected = Number(btn.dataset.value);
                ratingValue.value = String(selected); //元々0だった評価値をselectedで書き換え
                rateSubmit.disabled = selected <= 0; //星ボタんが押されていない時は「送信」を押せないようにする
                paintStars(selected); //上に書いた関数で星を塗りつぶす
            });
        });
        // 販売者用
        @if($position === 'seller' && $needsEvaluation)
        openModal();
        @endif
    </script>
</body>

</html>