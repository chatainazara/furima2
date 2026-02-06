<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evaluation;
use Illuminate\Support\Facades\Mail;
use App\Mail\TransactionRatedMail;
use App\Models\Buy;


class EvaluationController extends Controller
{
    public function store(Request $request ,$buyId){
        Evaluation::create([
            'buy_id'  => $buyId,
            'evaluate'  => $request->rating,
            'evaluated'  => null,
        ]);

        $buy = Buy::with(['item.user'])->findOrFail($buyId);
        // 出品者（販売者）のメール宛に送る
        $sellerUser = $buy->item->user;

        Mail::to($sellerUser->email)->send(
            new TransactionRatedMail(
                buyerName: auth()->user()->name,
                itemName: $buy->item->name,
            )
        );

        return redirect('/');
    }

    public function update(Request $request ,$buyId){
        Evaluation::where('buy_id', $buyId)->update([
            'evaluated'  => $request->rating,
        ]);
        return redirect('/');
    }
}
