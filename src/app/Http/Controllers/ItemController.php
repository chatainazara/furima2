<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Favorite;
use App\Models\Category;
use App\Models\Buy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use App\Http\Requests\ExhibitionRequest;
use App\Models\Comment;
use App\Http\Requests\CommentRequest;

class ItemController extends Controller
{
    public function index(Request $request)
    {
            $remove_items = Item::where('user_id',Auth::id())->get()->toArray();
            $items = Item::whereNotIn('id',Arr::pluck($remove_items,'id'))->get()->toArray();
            $buys = Buy::all();
            return view('index',[
                'items' => $items,
                'search' => "",
                'buys' => $buys,
            ]);
    }

    public function searchAndMylist(Request $request)
    {
        $tab = $request -> query('tab');
        // マイリストがクリックされたとき
        $buys = Buy::all();
        if ($tab == 'mylist'){
            // お気に入りに登録したアイテムを抽出
            $favorites = Favorite::where('user_id',Auth::id())->get()->toArray();
            // アイテム全体からお気に入りを抽出し検索窓に入力した値で検索
            $items = Item::whereIn('id',Arr::pluck($favorites,'item_id'))->NameSearch($request->search)->get();
            return view('index',['items'=> $items,'search'=>$request->search,'buys' => $buys]);
        }else{
            // 通常の検索
            // 自分の出品したもののid取得
            $removeItems = Item::where('user_id',Auth::id())->get()->toArray();
            // 自分が出品した商品の除去及び名前による検索
            $items = Item::whereNotIn('id',Arr::pluck($removeItems,'id'))->NameSearch($request->search)->get()->toArray();
            return view('index',['items'=> $items,'search'=>$request->search,'buys' => $buys]);
        }
    }

    public function sell(){
        $categories = Category::All();
        return view('auth.sell',['categories' => $categories]);
    }

    public function sellRegister(ExhibitionRequest $request){
        $form = $request->all();
        $newid = Item::max('id') + 1;
            $fileName = $request -> file('pict_url') -> getClientOriginalExtension();
            $request->file('pict_url')->storeAs('/public','item'.$newid.'.'.$fileName);
            $form['pict_url'] = 'storage/item'.$newid.'.'.$fileName;
        Item::create([
            'user_id' => Auth::id(),
            'name' => $form['name'],
            'pict_url' => $form['pict_url'],
            'brand_name' => $form['brand_name'],
            'price' => $form['price'],
            'detail' => $form['detail'],
            'condition' => $form['condition'],
        ]);
        $categories = array_values($form['categories']);
        foreach($categories as $category){
            $category_collection = Category::find($category);
            $category_collection->items()->attach($newid);
        }
        return redirect ('/');
    }

    public function itemDetailView(Request $request){
        $itemId = $request -> itemId;
        $item = Item::with('categories')->find($itemId);
        $userId = Auth::id();
        $favorites = Favorite::where('item_id',$itemId)->get();
        $favoritesCount = count($favorites);
        $buys = Buy::all();
        if ($favorites -> contains('user_id',$userId)){
            $favorite = 'favorite';
        }else{
            $favorite = 'un_favorite';
        }
        $comments = Comment::with(['user.profile'])->where('item_id',$itemId)->get();
        $commentsCount = count($comments);
        if ($comments -> contains('user_id',$userId)){
            $comment = 'comment';
        }else{
            $comment = 'un_comment';
        }
        return view('item_detail',[
            'item' => $item,
            'user_id' => $userId,
            'comments' => $comments,
            'comments_count' => $commentsCount,
            'comment' => $comment,
            'favorites' => $favorites,
            'favorites_count' => $favoritesCount,
            'favorite' => $favorite,
            'search' => '',
            'buys' => $buys,
        ]);
    }

    public function itemDetail(CommentRequest $request)
    {
        $itemId = $request -> itemId;
        $item = Item::with('categories')->find($itemId);
        $userId = Auth::id();
        $favorites = Favorite::where('item_id',$itemId)->get();
        $favoritesCount = count($favorites);
        $buys = Buy::all();
        if ($favorites -> contains('user_id',$userId)){
            $favorite = 'favorite';
        }else{
            $favorite = 'un_favorite';
        }
        $comments = Comment::with(['user.profile'])->where('item_id',$itemId)->get();
        $commentsCount = count($comments);
        if ($comments -> contains('user_id',$userId)){
            $comment = 'comment';
        }else{
            $comment = 'un_comment';
        }
        switch ($request->input('action')) {
            // いいね
            case 'favorite':
                Favorite::create([
                    'item_id'=>$itemId,
                    'user_id'=>$userId,
                ]);
                $favorite = 'favorite';
                return view('item_detail',[
                    'item' => $item,
                    'user_id' => $userId,
                    'comments' => $comments,
                    'comments_count' => $commentsCount,
                    'comment' => $comment,
                    'favorites' => $favorites,
                    'favorites_count' => $favoritesCount +1,
                    'favorite' => $favorite,
                    'search' => '',
                    'buys' => $buys,
                ]);
            // いいね解除
            case 'un_favorite':
                Favorite::where('user_id',Auth::id())->where('item_id',$itemId)->delete();
                $favorite = 'un_favorite';
                    return view('item_detail',[
                        'item' => $item,
                        'user_id' => $userId,
                        'comments' => $comments,
                        'comments_count' => $commentsCount,
                        'comment' => $comment,
                        'favorites' => $favorites,
                        'favorites_count' => $favoritesCount -1,
                        'favorite' => $favorite,
                        'search' => '',
                        'buys' => $buys,
                    ]);
            // コメント投稿
            case 'comment':
                if (!auth()->check()) {
                    return back();
                }
                $content = $request -> content;
                Comment::create([
                    'item_id' => $itemId,
                    'user_id' => $userId,
                    'content' => $content
                ]);
                $comments = Comment::with(['user.profile'])->where('item_id',$itemId)->get();
                if ($comments -> contains('user_id',$userId)){
                    $comment = 'comment';
                }else{
                    $comment = 'un_comment';
                }
            return view('item_detail',[
                'item' => $item,
                'user_id' => $userId,
                'comments' => $comments,
                'comments_count' => $commentsCount + 1,
                'comment' => $comment,
                'favorites' => $favorites,
                'favorites_count' => $favoritesCount,
                'favorite' => $favorite,
                'search' => '',
                'buys' => $buys,
            ]);
        }
    }
}
