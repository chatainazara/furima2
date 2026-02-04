<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Buy;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
    'buy_id',
    'chat',
    'pict',
    'position',
    ];

    public function buy(){
        return $this->belongsTo(Buy::class);
    }

}
