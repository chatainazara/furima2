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
                    $filledStars = floor($avgRating);
                @endphp
                @for ($i = 1; $i <= 5; $i++)
                    @if ($i <= $filledStars)
                        <span class="star filled">★</span>
                    @else
                        <span class="star">★</span>
                    @endif
                @endfor
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
                @csrf
                <input type="hidden" name="search" value="{{$search}}">
                <button class="list-top__button  {{($tab??'')==='transaction'?'is-active':''}}" type="submit">取引中商品の商品</button>
            </form>
        </div>
    </div>

    <div class="list">
        @foreach($items as $item)
        <div class="list__content">
            <div class="list__content-img">
                @if(!empty($transaction))
                <form class="list__content--form" action="/transaction/{{$item->id}}" method="get" >
                @else
                <form class="list__content--form" action="/item/{{$item->id}}" method="get" >
                @endif
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
        @endforeach
    </div>
</div>
@endsection
