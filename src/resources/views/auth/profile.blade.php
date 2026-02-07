@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('content')
<div class="content">

    <!-- プロフィール -->
    <div class="profile__group">
        <div class="profile__inner">
            <div class="profile__img">
                <img class="profile__img--item" src="{{asset($profile->pict_url ?? '')}}" alt=""/>
            </div>
            <div class="profile__name">
                <div class="profile__name--text">
                    {{$user['name']}}
                </div>
                <div class="profile__name--star">
                @php
                    $filledStars = round($avgRating);
                @endphp
                @if(!empty($filledStars))
                    @for ($i = 1; $i <= 5; $i++)
                        @if ($i <= $filledStars)
                            <span class="star filled">★</span>
                        @else
                            <span class="star">★</span>
                        @endif
                    @endfor
                @endif
                </div>
            </div>
            <form class="profile__edit" action="/mypage/profile" method="get">
                @csrf
                <button class="profile__edit--button" type="submit">プロフィールを編集</button>
            </form>
        </div>
    </div>

    <div class="list-top">
        <div class="list-top__inner">
            <form class="list-top__form" action="/mypage?tab=sell" method="post">
                @csrf
                <input type="hidden" name="search" value="{{$search}}">
                <button class="list-top__button {{($tab??'')==='sell'?'is-active':''}}" type="submit">出品した商品</button>
            </form>
            <form class="list-top__form" action="/mypage?tab=buy" method="post">
                @csrf
                <input type="hidden" name="search" value="{{$search}}">
                <button class="list-top__button {{($tab??'')==='buy'?'is-active':''}}" type="submit">購入した商品</button>
            </form>
            <form class="list-top__form" action="/mypage/transaction" method="get">
                <input type="hidden" name="search" value="{{$search}}">
                @php $totalUnread = $totalUnread ?? 0; @endphp
                <button class="list-top__button  {{($tab??'')==='transaction'?'is-active':''}}" type="submit">
                    取引中の商品
                @if($totalUnread > 0)
                    <span class="badge">{{ $totalUnread }}</span>
                @endif
                </button>

            </form>
        </div>
    </div>

    <div class="list">
        @foreach($items as $item)
        @if(empty($transaction))
        <!-- 出品した商品と購入した商品のタブの場合 -->
        <div class="list__content">
            <div class="list__content-img">
                <form class="list__content--form" action="/item/{{$item->id}}" method="get" >
                    @csrf
                    <button class="list__content--button" name="action" value="detail" type="submit">
                        <img class="list__content--pict" src="{{asset($item->pict_url)}}" alt="" />
                    </button>
                </form>
                @if($buys->contains('item_id', $item->id))
                <div class="list__content-img--attention" >
                    <p class="list__content-img--attention-text" >sold</p>
                </div>
                @endif
            </div>
            <div class="list__content-explain">
                <p class="list__content-text">{{$item['name']}}</p>
            </div>
        </div>
        @else
        <!-- 取引中の商品のタブの場合 -->
        <div class="list__content">
            <div class="list__content-img">
                @php
                    $buy = $buys->firstWhere('item_id', $item->id);     // この商品に紐づく取引(Buy)を1件取る
                    $eval  = $buy?->evaluation ?? null;                   // 評価があれば取る
                    $isDone = $eval && !is_null($eval->evaluate) && !is_null($eval->evaluated); // 両方埋まってたら完了
                @endphp
                @if($isDone)
                <!-- 取引が終了した商品 -->
                <form class="list__content--form" action="/transaction/{{$item->id}}" method="get" >
                    <button class="list__content--button" name="action" value="detail" type="button">
                        <img class="list__content--pict" src="{{asset($item->pict_url)}}" alt="" />
                    </button>
                </form>
                <div class="list__content-img--attention" >
                    <p class="list__content-img--attention-text" >取引終了</p>
                </div>
                @else
                <!-- まだ取引中の商品 -->
                <form class="list__content--form" action="/transaction/{{$item->id}}" method="get" >
                    @php
                        $unread = $unreadCountByItem[$item->id] ?? 0;
                    @endphp
                    @if($unread > 0)
                        <div class="unread-badge">{{ $unread }}</div>
                    @endif
                    <button class="list__content--button" name="action" value="detail" type="submit">
                        <img class="list__content--pict" src="{{asset($item->pict_url)}}" alt="" />
                    </button>
                </form>
                @endif
            </div>
            <div class="list__content-explain">
                <p class="list__content-text">{{$item['name']}}</p>
            </div>
        </div>
        @endif
        @endforeach
    </div>
</div>
@endsection
