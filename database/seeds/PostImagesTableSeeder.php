<?php

use Illuminate\Database\Seeder;
use App\Models\PostImage;
use Carbon\Carbon;

class PostImagesTableSeeder extends Seeder
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

        $path = base_path().'/public_html/'.config('hwimages.newsPost.destination');

        foreach (range(1,10) as $key => $index) {
        	PostImage::create([
                    'title' => str_replace('.', '', $faker->sentence(2)),
                    'active' => $faker->randomElement($array = array('active','inactive')),
                    'post_id' => $faker->numberBetween($min = 1, $max = 5),
                    'image' => $faker->image($path, 500, 500, 'transport', false),
                    'created_at' => Carbon::now(),
    				'updated_at' => Carbon::now(),  
                    'position' => $key+1                  
                ]);
        }
    }
}
