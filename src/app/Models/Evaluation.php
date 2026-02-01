<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Item;
use App\Models\User;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'item_id',
        'evaluate',
        'evaluated',
        'user_id',
    ];

    public function item(){
        return $this->belongsTo('App\Models\Item');
    }

    public function user(){
        return $this->belongsTo('App\Models\User');
    }

}
