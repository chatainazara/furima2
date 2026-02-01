<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use App\Models\Chat;

class TransactionController extends Controller
{
    public function transaction($itemId){
        $item = Item::find($itemId);
        $user = User::with('profile')->find(Auth::id());
        return view('auth.transaction',compact('item','user'));
    }
}
