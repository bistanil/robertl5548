<?php

use Illuminate\Database\Seeder;
use App\Models\PostComment;
use Carbon\Carbon;

class PostCommentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();

        $faker = Faker\Factory::create();

        foreach (range(1,10) as $key => $index) {
        	PostComment::create([
                    'post_id' => $faker->numberBetween($min = 1, $max = 5),
                    'status' => $faker->randomElement($array = array('approved','rejected','new')),
                    'author' => $faker->name(),
                    'email' => $faker->email(),
                    'content' => $faker->realText(500), 
                    'created_at' => Carbon::now(),
    				'updated_at' => Carbon::now(),                  
                ]);
        }
    }
}
