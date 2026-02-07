<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EvaluationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'buy_id' => 5,
            'evaluate' => 5,
            'evaluated' => 1,
        ];
        DB::table('evaluations')->insert($param);
    }
}
