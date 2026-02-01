<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->integer('evaluate');//購入者からみた販売者の評価
            $table->integer('evaluated');//販売者からみた購入者の評価
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();//購入者のid、購入前の段階でも良い
        });
    }
    //取引チャットは商品を購入していなくても可能なことから、一つのアイテムに対し、複数の購入者・販売者ペアが存在しうる。そのため　item_id　はuniqueではない。

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('evaluations');
    }
}
