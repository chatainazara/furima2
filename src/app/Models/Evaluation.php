<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Buy;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'buy_id',
        'evaluate',
        'evaluated',
    ];

    public function buy(){
        return $this->belongsTo('App\Models\Buy');
    }

}
