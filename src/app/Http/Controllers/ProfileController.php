<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ProfileRequest;
use App\Models\Buy;
use App\Models\Item;
use App\Models\Chat;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function edit(){
        if(Profile::where('user_id',Auth::id())->exists()){
            $id = Auth::id();
            $profile = Profile::where('user_id',$id)->first();
            return view('auth.profile_edit',['search' =>'' ,'profile' => $profile]);
        }else{
            return view('auth.profile_edit',['search' => '']);
        }
    }

    public function update(ProfileRequest $request){
        $form = $request -> all();
        if($request -> file('pict_url') != ''){
            $fileName = $request -> file('pict_url') -> getClientOriginalExtension();
            $request->file('pict_url')->storeAs('/public','profile'.Auth::id().'.'.$fileName);
            $form['pict_url'] = 'storage/profile'.Auth::id().'.'.$fileName;
        }
        unset($form['_token']);
        Profile::updateOrCreate(
            ['user_id' => Auth::id()],
            $form
            );
        User::find(Auth::id()) -> update(['name' => $form['name']]);
        return redirect('/');
    }

    public function profile(Request $request){
        $user = User::find(Auth::id());
        $profile = Profile::where('user_id',Auth::id())->first();
        $items = Item::where('user_id',Auth::id())->get();
        $buys = Buy::all();
        $userId = $user->id;
        $avgRating = $this->buildAvgRating($userId);
        [$unreadCountByBuy, $unreadCountByItem, $totalUnread] = $this->buildUnreadBadges($userId);
        return view('auth.profile',['profile' => $profile,'user'=>$user,'items' => $items,'search'=>$request->search,'buys' => $buys,'avgRating'=>$avgRating,  'unreadCountByBuy' => $unreadCountByBuy,'unreadCountByItem' => $unreadCountByItem,'totalUnread' => $totalUnread,]);
    }

    public function buyOrSell(Request $request){
        $tab = $request -> query('tab');
        $buys = Buy::all();
        if($tab == 'buy'){
            $user = User::find(Auth::id());
            $profile = Profile::where('user_id',Auth::id())->first();
            $buyId = Buy::where('user_id',Auth::id())->pluck('item_id')->toArray();
            $items = Item::whereIn('id',$buyId)->get();
            $userId = $user->id;
            $avgRating = $this->buildAvgRating($userId);
            [$unreadCountByBuy, $unreadCountByItem, $totalUnread] = $this->buildUnreadBadges($userId);
            return view('auth.profile',['profile' => $profile,'user'=>$user,'items' => $items,'search'=>$request->search,'buys' => $buys,'tab'=>'buy','avgRating'=>$avgRating, 'unreadCountByBuy' => $unreadCountByBuy,'unreadCountByItem' => $unreadCountByItem,'totalUnread' => $totalUnread,]);
        }else{
            $user = User::find(Auth::id());
            $profile = Profile::where('user_id',Auth::id())->first();
            $items = Item::where('user_id',Auth::id())->get();
            $userId = $user->id;
            $avgRating = $this->buildAvgRating($userId);
            [$unreadCountByBuy, $unreadCountByItem, $totalUnread] = $this->buildUnreadBadges($userId);
            return view('auth.profile',['profile' => $profile,'user'=>$user,'items' => $items,'search'=>$request->search,'buys' => $buys,'tab'=>'sell','avgRating'=>$avgRating, 'unreadCountByBuy' => $unreadCountByBuy,'unreadCountByItem' => $unreadCountByItem,'totalUnread' => $totalUnread,]);
        }
    }

    public function transaction(){
        $userId = Auth::id();
        $user = User::find($userId);
        $profile = Profile::where('user_id',$userId)->first();
        $buys = Buy::all();
        // 自分が購入した商品ID
        $buyItemIds = Buy::where('user_id', $userId)
            ->pluck('item_id');
        // 自分が出品者の商品で、売れた商品のid
        $sellItemIds = Item::whereHas('buys')
            ->where('user_id',$userId)
            ->pluck('id');
        // 売買履歴のある商品のコレクション（最新チャット順）
        $items = Item::whereIn('items.id', $buyItemIds)
            ->orWhereIn('items.id', $sellItemIds)
            ->leftJoin('buys', 'buys.item_id', '=', 'items.id')
            ->leftJoin('chats', 'chats.buy_id', '=', 'buys.id')
            ->select('items.*', DB::raw('MAX(chats.created_at) as latest_chat_at'))
            ->groupBy('items.id')
            ->orderByDesc('latest_chat_at')
            ->get();
        $avgRating = $this->buildAvgRating($userId);
        [$unreadCountByBuy, $unreadCountByItem, $totalUnread] = $this->buildUnreadBadges($userId);
        return view('auth.profile',['profile' => $profile,'user'=>$user,'items' => $items,'buys' => $buys ,'transaction' => true,'tab'=>'transaction','avgRating'=>$avgRating,'unreadCountByBuy' => $unreadCountByBuy,'unreadCountByItem' => $unreadCountByItem,'totalUnread' => $totalUnread,]);
    }

    private function buildUnreadBadges(int $userId): array
    {
        // 未読バッジ
        $unreadCountByBuy = Chat::join('buys', 'buys.id', '=', 'chats.buy_id')
            ->join('items', 'items.id', '=', 'buys.item_id')
            ->where(function ($query) use ($userId) {
                $query->where(function ($query2) use ($userId) {
                        // 自分が買い手の時の相手（売り手）の発言だけ
                        $query2->where('buys.user_id', $userId)
                        ->where('chats.position', 'seller')
                        ->where(function ($query3) {
                            $query3->whereNull('buys.buyer_read')
                                ->orWhereColumn('chats.id', '>', 'buys.buyer_read');
                        });
                    })
                ->orWhere(function ($query2) use ($userId) {
                        // 自分が売り手の時の相手（買い手）の発言だけ
                        $query2->where('items.user_id', $userId)
                        ->where('chats.position', 'buyer')
                        ->where(function ($query3) {
                            $query3->whereNull('buys.seller_read')
                                ->orWhereColumn('chats.id', '>', 'buys.seller_read');
                        });
                    });
            })
            ->select('buys.id as buy_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('buys.id')
            ->pluck('cnt', 'buy_id');
        // itemごとに合算
        $itemIdByBuyId = Buy::whereIn('id', $unreadCountByBuy->keys())
            ->pluck('item_id', 'id');
        $unreadCountByItem = [];
        foreach ($unreadCountByBuy as $buyId => $cnt) {
            $itemId = $itemIdByBuyId[$buyId] ?? null;
            if (!$itemId) continue;
            $unreadCountByItem[$itemId] = ($unreadCountByItem[$itemId] ?? 0) + $cnt;
        }
        $totalUnread = $unreadCountByBuy->sum();
        return [$unreadCountByBuy, $unreadCountByItem, $totalUnread];
    }

    private function buildAvgRating(int $userId): ?float
    {
        $evaluations = Buy::with(['evaluation', 'item'])
            ->where('user_id', $userId) // 買い手として関与
            ->orWhereHas('item', fn($query) => $query->where('user_id', $userId)) // 売り手として関与
            ->get()
            ->pluck('evaluation')
            ->filter(); // nullを除去
        if ($evaluations->isEmpty()) return null;
        $sum = 0;
        $count = 0;
        foreach ($evaluations as $eval) {
            // 自分が売り手のとき受けた評価
            if ($eval->buy->item->user_id == $userId && !is_null($eval->evaluate)) {
                $sum += $eval->evaluate;
                $count++;
            }
            // 自分が買い手のとき受けた評価
            if ($eval->buy->user_id == $userId && !is_null($eval->evaluated)) {
                $sum += $eval->evaluated;
                $count++;
            }
        }
        return $count === 0 ? null : $sum / $count;
    }
}