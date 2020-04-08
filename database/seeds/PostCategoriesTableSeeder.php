<?php

use Illuminate\Database\Seeder;
use App\Models\PostCategory;
use Carbon\Carbon;

class PostCategoriesTableSeeder extends Seeder
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
            PostCategory::create([
                    'category_id' => $faker->numberBetween($min = 1, $max = 5),
                    'post_id' => $faker->numberBetween($min = 1, $max = 5),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),               
                ]);
        }
    }
}
