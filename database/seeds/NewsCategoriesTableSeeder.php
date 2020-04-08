<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\NewsCategory;
use Carbon\Carbon;

class NewsCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Eloquent::unguard();

        $faker= Faker\Factory::create();

        foreach (range(1,5) as $key => $index) {
            $title = str_replace('.', '', $faker->sentence(2));
            $slug = str_slug($title, "-");
            NewsCategory::create([
                    'active' => $faker->randomElement($array = array('active','inactive')),
                    'language' => locale(),
                    'position' => $key+1,  
                    'title' => $title,
                    'slug' => $slug,
                    'meta_title' => str_replace('.', '', $faker->sentence(3)),
                    'meta_keywords' => str_replace('.', '', $faker->sentence(3)),
                    'meta_description' => $faker->realText(50),
                    'content' => $faker->realText(500),                
                ]);
        }
    }
}
