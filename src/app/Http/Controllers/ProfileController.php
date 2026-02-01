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
use App\Models\Evaluation;

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
        $evaluate = Evaluation::whereHas('item',function($query){$query->where('user_id',Auth::id());})->get();
        $evaluated = Evaluation::where('user_id',Auth::id());
        $evaluateAvg   = $evaluate->avg('evaluate');
        $evaluateCount = $evaluate->count();
        $evaluatedAvg   = $evaluated->avg('evaluated');
        $evaluatedCount = $evaluated->count();
        $totalCount = $evaluateCount + $evaluatedCount;
        $avgRating = $totalCount > 0 ? (($evaluateAvg * $evaluateCount) +  ($evaluatedAvg * $evaluatedCount)) / $totalCount : null;
        return view('auth.profile',['profile' => $profile,'user'=>$user,'items' => $items,'search'=>$request->search,'buys' => $buys,'avgRating'=>$avgRating]);
    }

    public function buyOrSell(Request $request){
        $tab = $request -> query('tab');
        $buys = Buy::all();
        if($tab == 'buy'){
            $user = User::find(Auth::id());
            $profile = Profile::where('user_id',Auth::id())->first();
            $buyId = Buy::where('user_id',Auth::id())->pluck('item_id')->toArray();
            $items = Item::whereIn('id',$buyId)->get();
            $evaluate = Evaluation::whereHas('item',function($query){$query->where('user_id',Auth::id());})->get();
            $evaluated = Evaluation::where('user_id',Auth::id());
            $evaluateAvg   = $evaluate->avg('evaluate');
            $evaluateCount = $evaluate->count();
            $evaluatedAvg   = $evaluated->avg('evaluated');
            $evaluatedCount = $evaluated->count();
            $totalCount = $evaluateCount + $evaluatedCount;
            $avgRating = $totalCount > 0 ? (($evaluateAvg * $evaluateCount) +  ($evaluatedAvg * $evaluatedCount)) / $totalCount : null;
            return view('auth.profile',['profile' => $profile,'user'=>$user,'items' => $items,'search'=>$request->search,'buys' => $buys,'tab'=>'buy','avgRating'=>$avgRating]);
        }else{
            $user = User::find(Auth::id());
            $profile = Profile::where('user_id',Auth::id())->first();
            $items = Item::where('user_id',Auth::id())->get();
            $evaluate = Evaluation::whereHas('item',function($query){$query->where('user_id',Auth::id());})->get();
            $evaluated = Evaluation::where('user_id',Auth::id());
            $evaluateAvg   = $evaluate->avg('evaluate');
            $evaluateCount = $evaluate->count();
            $evaluatedAvg   = $evaluated->avg('evaluated');
            $evaluatedCount = $evaluated->count();
            $totalCount = $evaluateCount + $evaluatedCount;
            $avgRating = $totalCount > 0 ? (($evaluateAvg * $evaluateCount) +  ($evaluatedAvg * $evaluatedCount)) / $totalCount : null;
            return view('auth.profile',['profile' => $profile,'user'=>$user,'items' => $items,'search'=>$request->search,'buys' => $buys,'tab'=>'sell','avgRating'=>$avgRating]);
        }
    }

    public function transaction(){
        $userId = Auth::id();
        // 自分が参加したチャットの商品ID
        $chatItemIds = Chat::where('user_id', $userId)
            ->pluck('item_id');
        // 自分が出品者の商品でチャットが存在するitemのid
        $transactionItemIds = Item::whereHas('chats')
            ->where('user_id',$userId)
            ->pluck('id');
        $items = Item::whereIn('id', $chatItemIds)
            ->orWhereIn('id', $transactionItemIds)
            ->get();
        $profile = Profile::where('user_id',Auth::id())->first();
        $user = User::find(Auth::id());
        $buys = Buy::all();
        $evaluate = Evaluation::whereHas('item',function($query){$query->where('user_id',Auth::id());})->get();
        $evaluated = Evaluation::where('user_id',Auth::id());
        $evaluateAvg   = $evaluate->avg('evaluate');
        $evaluateCount = $evaluate->count();
        $evaluatedAvg   = $evaluated->avg('evaluated');
        $evaluatedCount = $evaluated->count();
        $totalCount = $evaluateCount + $evaluatedCount;
        $avgRating = $totalCount > 0 ? (($evaluateAvg * $evaluateCount) +  ($evaluatedAvg * $evaluatedCount)) / $totalCount : null;
        return view('auth.profile',['profile' => $profile,'user'=>$user,'items' => $items,'buys' => $buys ,'transaction' => true,'tab'=>'transaction','avgRating'=>$avgRating]);
    }

}