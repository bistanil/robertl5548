<?php

use Illuminate\Database\Seeder;
use App\Models\Slide;
use Carbon\Carbon;

class SlidesTableSeeder extends Seeder
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

        $path = base_path().'/public_html/'.config('hwimages.slide.destination');

        foreach (range(1,5) as $key => $index) {
            Slide::create([
                    'title' => str_replace('.', '', $faker->sentence(2)),
                    'content' => $faker->paragraph($nbSentences = 3, $variableNbSentences = true),
                    'active' => $faker->randomElement($array = array('active','inactive')),
                    'language' => App::getLocale(),
                    'link' => $faker->url(),
                    'image' => $faker->image($path, 1500, 500, 'abstract', false),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),  
                    'position' => $key+1                  
                ]);
        }
    }
}
