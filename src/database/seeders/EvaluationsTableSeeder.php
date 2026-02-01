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
            'item_id' => 3,
            'evaluate' => 4,
            'evaluated' => 5,
            'user_id' => 3,
        ];
        DB::table('evaluations')->insert($param);
        $param = [
            'item_id' => 3,
            'evaluate' => 4,
            'evaluated' => 3,
            'user_id' => 2,
        ];
        DB::table('evaluations')->insert($param);
        $param = [
            'item_id' => 6,
            'evaluate' => 4,
            'evaluated' => 3,
            'user_id' => 1,
        ];
        DB::table('evaluations')->insert($param);
    }
}
