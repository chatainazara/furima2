@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="list-top">
        <div class="list-top__inner">
            <div class="list-top__link">
                <p class="list-top__link-text {{ request('tab') === 'mylist' ? '' : 'is-active' }}">おすすめ</p>
            </div>
            <form class="list-top__form" action="/?tab=mylist" method="post">
                @csrf
                <input type="hidden" name="search" value="{{$search}}">
                <button class="list-top__button  {{ request('tab') === 'mylist' ? 'is-active' : '' }}" type="submit">マイリスト</button>
            </form>
        </div>
    </div>

    <div class="list">
        @foreach($items as $item)
        <div class="list__content">
            <div class="list__content-img">
                <form class="list__content--form" action="/item/{{$item['id']}}" method="get" >
                    @csrf
                    <button class="list__content--button" name="action" value="detail" type="submit">
                        <img class="list__content--pict" src="{{$item['pict_url']}}" alt="" />
                    </button>
                </form>
                @if($buys->contains('item_id', $item['id']))
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
