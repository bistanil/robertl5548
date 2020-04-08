<?php

use Illuminate\Database\Seeder;
use App\Models\Partner;

class PartnersTableSeeder extends Seeder
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
        	Partner::create([
                    'title' => $faker->sentence(5),
                    'content' => $faker->realText(250),
                    'active' => 'active',
                    'language' => 'en',
                    'link' => $faker->url(),
                    'image' => $faker->imageUrl($width = 640, $height = 480)                    
                ]);
        }
    }
}
