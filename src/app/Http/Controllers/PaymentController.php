<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\Item;
use App\Models\Buy;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PurchaseRequest;

class PaymentController extends Controller
{
    public function index(PurchaseRequest $request, Item $item)
    {
        $payment = $request->payment;
        session([
            'user_id' => Auth::id(),
            'item_id' => $item->id,
            'payment' => $payment,
            'destination_post_code' => $request->destination_post_code,
            'destination_address' => $request->destination_address,
            'destination_building' => $request->destination_building,
        ]);
        return view('auth.payment', compact('item','payment'));
    }

    public function store(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $amount = Item::where('id', session('item_id'))->value('price');
            $payment = session('payment') ?? 'card';
            $paymentIntentData = [
                'amount' => $amount,
                'currency' => 'jpy',
                'payment_method_types' => $payment === 'konbini' ? ['konbini'] : ['card'],
            ];
            if ($payment === 'card' && $request->payment_method) {
                $paymentIntentData['payment_method'] = $request->payment_method;
                $paymentIntentData['confirm'] = true;
            }
            $paymentIntent = PaymentIntent::create($paymentIntentData);
            // buysに即時保存（カードもコンビニも同じ扱い）
            Buy::create([
                'user_id'              => session('user_id'),
                'item_id'              => session('item_id'),
                'payment'              => $payment,
                'destination_post_code'=> session('destination_post_code'),
                'destination_address'  => session('destination_address'),
                'destination_building' => session('destination_building'),
            ]);
            // セッションをクリア
            session()->forget([
                'payment',
                'destination_post_code','destination_address','destination_building'
            ]);
            return response()->json([
                'success' => true,
                'message' => $payment === 'card' ? 'カード決済完了！' : 'コンビニ支払い完了扱い！',
                'redirect' => '/transaction/'.session('item_id')
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
