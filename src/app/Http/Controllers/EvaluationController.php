<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evaluation;

class EvaluationController extends Controller
{
    public function store(Request $request ,$buyId){
        Evaluation::create([
            'buy_id'  => $buyId,
            'evaluate'  => $request->rating,
            'evaluated'  => null,
        ]);
        return redirect('/');
    }

    public function update(Request $request ,$buyId){
        Evaluation::where('buy_id', $buyId)->update([
            'evaluated'  => $request->rating,
        ]);
        return redirect('/');
    }
}
