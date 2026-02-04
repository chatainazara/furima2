<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\EvaluationController;

// ログインが必要なルート
Route::middleware(['auth','verified'])->group(function () {
    Route::get('/mypage/profile',[ProfileController::class, 'edit']);
    Route::post('/mypage/profile',[ProfileController::class, 'update']);
    // 未承認のログインユーザーは下記のルートで必ずプロフィールを設定する
    Route::middleware(['profile.set'])->group(function () {
        Route::get('/sell', [ItemController::class, 'sell']);
        Route::post('/sell', [ItemController::class, 'sellRegister']);
        Route::get('/mypage', [ProfileController::class, 'profile']);
        Route::post('/mypage', [ProfileController::class, 'buyOrSell']);
        Route::get('/mypage/transaction', [ProfileController::class, 'transaction']);//追加
        Route::get('/purchase/{itemId}',[PurchaseController::class,'purchaseView']);
        Route::get('/purchase/address/{itemId}',[PurchaseController::class,'destinationInput']);
        Route::post('/purchase/address/{itemId}',[PurchaseController::class,'destinationOrPaymentChange']);
        Route::post('/item/{itemId}', [ItemController::class, 'itemDetail']);
        Route::get('/transaction/{itemId}', [TransactionController::class, 'transaction']);//追加
        Route::post('/transaction/store', [TransactionController::class, 'store']);//追加
        Route::post('/transaction/update/{chat}', [TransactionController::class, 'update']);//追加
        Route::delete('/transaction/delete/{chat}', [TransactionController::class, 'delete']);//追加
        Route::post('/evaluation/store/{buyId}', [EvaluationController::class, 'store']);
        Route::post('/evaluation/update/{buyId}', [EvaluationController::class, 'update']);
    });
    // stripe決済
    Route::get('/payment/{item}', [PaymentController::class, 'index']);
    Route::post('/payment/store', [PaymentController::class, 'store']);
});

// ログイン不要のルート
Route::get('/', [ItemController::class, 'index']);
Route::post('/',[ItemController::class,'searchAndMylist']);
Route::get('/item/{itemId}', [ItemController::class, 'itemDetailView']);

//phpinfoによる情報確認
Route::get('/phpinfo', function(){return view('/phpinfo');});

// mailhogによる認証ルート
Route::get('/email/verify', function () {
    return view('auth.verify_email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
        return redirect('/mypage/profile');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');
