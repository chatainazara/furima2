<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Chat;
use App\Models\Buy;
use App\Http\Requests\TransactionRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Evaluation;

class TransactionController extends Controller
{
    public function transaction(Request $request ,$itemId){
        $needsEvaluation = false; //評価modalのためのフラッグ
        $transactionItem = Buy::with(['item.user.profile'])
            ->where('item_id', $itemId)
            ->first();
        $seller = $transactionItem->item->user;
        $buyer = $transactionItem->user;
        $userId = Auth::id();
        if ($userId === $seller->id) {// 自分が売り手の時
            $chats = Chat::with('buy.item.user.profile')
                ->whereHas('buy.item', function ($query) use ($itemId) {
                    $query->where('id', $itemId);
                })
                ->get();
            //Buyを起点としたものも取得しないとchatの数だけitemが重複して取得される
            $buys = Buy::with(['item.user.profile','evaluation'])
                ->whereHas('item', function ($query) use ($userId,$itemId) {
                    $query->where('user_id', $userId)->where('item_id', '!=', $itemId);//修正
                })
                ->leftJoin('chats', 'buys.id', '=', 'chats.buy_id')
                ->select('buys.*', DB::raw('MAX(chats.created_at) as last_chat_at'))
                ->groupBy('buys.id')
                ->orderByDesc('last_chat_at')
                ->get();
            $position = 'seller';
            $needsEvaluation = Evaluation::where('buy_id', $transactionItem->id)
                ->whereNotNull('evaluate')      // 購入者が評価済み
                ->whereNull('evaluated')        // 販売者はまだ
                ->exists();
        } else {// 自分が買い手の場合
            $chats = Chat::with('buy.item.user.profile')
                ->whereHas('buy.item', function ($query) use ($itemId) {
                    $query->where('id', $itemId);
                })
                ->get();
            $buys = Buy::with(['item.user.profile','evaluation'])
                ->where('user_id', $userId)->where('item_id', '!=', $itemId)//修正
                ->get();
            $position = 'buyer';
            $needsEvaluation = Evaluation::where('buy_id', $transactionItem->id)
                ->whereNotNull('evaluate')      // 購入者が評価済み
                ->exists();
        }
        $editId = $request->query('editId');
        // 既読ロジック
        $buyId = $transactionItem->id;
        $buy = Buy::with('item')->findOrFail($buyId);
        $userId = Auth::id();
        $latestOtherId = Chat::where('buy_id', $buyId)
            ->where('position', $position === 'buyer' ? 'seller' : 'buyer') // 相手側
            ->max('id');
        if ($latestOtherId) {
            if ($position === 'buyer') $buy->buyer_read = $latestOtherId;
            if ($position === 'seller') $buy->seller_read = $latestOtherId;
            $buy->save();
        }
        // 既読ロジック終わり
        return view('auth.transaction',compact('buys','transactionItem','chats','position','userId','editId','needsEvaluation'));
    }

    public function update(TransactionRequest $request ,Chat $chat){
        $chat->update([
            'chat' => $request->chat,
        ]);
        return redirect('/transaction/'.$chat->buy->item->id);
    }

    public function store(TransactionRequest $request)
    {
        // 画像保存
        $readPath = null;
        if ($request->hasFile('pict')) {
            $fileName = $request -> file('pict') -> getClientOriginalExtension();
            $buyId = $request->buyId;
            $maxChatId = Chat::where('buy_id', $buyId)->max('id');
            $savePath = $request->file('pict')->storeAs('/public','chat'.$maxChatId.'.'.$fileName);
            $readPath = 'storage/chat'.$maxChatId.'.'.$fileName;
        }
        Chat::create([
            'buy_id'   => $request->buyId,
            'chat'     => $request->chat,
            'pict'     => $readPath,
            'position' => $request->position,
        ]);
        // 下書き消す
        session()->forget('chat_draft');
        return redirect()->back();
    }

    public function delete(Request $request)
    {
        Chat::find($request->chat)->delete();
        return redirect()->back();
    }

}
