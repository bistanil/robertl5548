<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\CatalogCategory;

class CatalogCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();

        $faker= Faker\Factory::create();

        foreach (range(1,5) as $index) {
        	$title=$faker->sentence(5);
        	$slug=str_slug($title, "-");
            CatalogCategory::create([
                    'catalog_id'=>2,
                    'parent' => 0,
                    'title' => $title,
                    'content' => $faker->realText(250),
                    'meta_title' => $faker->sentence(5),
                    'meta_keywords' => $faker->sentence(10),
                    'meta_description' => $faker->realText(50),
                    'active' => 'active',
                    'language' => 'en',
                    'slug' => $slug,                    
                ]);
        }
    }
}