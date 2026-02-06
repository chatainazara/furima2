<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Item;

class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $userIds = User::pluck('id')->all();
        $itemIds = Item::select('id','user_id')->get()->toArray();
        $id=1;
        foreach($userIds as $userId){
            foreach($itemIds as $itemId){
                $mix[] = ['id'=>$id,'user_id'=>$userId,'item_id'=>$itemId['id']];
                $id++;
            }
        }
        $count = count($mix);
        $fakeId = $this->faker->numberBetween(1,$count);
        $key = array_search($fakeId, array_column($mix, "id"));
        $userId = $mix[$key]['user_id'];
        $itemId = $mix[$key]['item_id'];

        return [
            'user_id' => $userId,
            'item_id' => $itemId,
            'content' => $this->faker->sentence(),
        ];
    }
}
