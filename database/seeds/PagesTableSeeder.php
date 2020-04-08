<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Page;
use Carbon\Carbon;

class PagesTableSeeder extends Seeder
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

        foreach (range(1,6) as $key => $index) {
            $title = str_replace('.', '', $faker->sentence(2));
            $slug = str_slug($title, "-");
            Page::create([
                    'active' => $faker->randomElement($array = array('active','inactive')),
                    'language' => locale(),
                    'position' => $key+1,  
                    'title' => $title,
                    'slug' => $slug,
                    'menu' => $faker->randomElement($array = array('top','footer','cookies','terms','withdrawal','policy')),
                    'meta_title' => str_replace('.', '', $faker->sentence(3)),
                    'meta_keywords' => str_replace('.', '', $faker->sentence(3)),
                    'meta_description' => $faker->realText(50),
                    'content' => $faker->realText(500),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),                
                ]);
        }
    }
}