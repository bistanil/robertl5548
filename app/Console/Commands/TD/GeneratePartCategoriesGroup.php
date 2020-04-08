<?php

namespace App\Console\Commands\TD;

use Illuminate\Console\Command;
use App\Models\PartsCategory;

class GeneratePartCategoriesGroup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generatepartcategoriesgroup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate category group by first ancestor';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $categories = PartsCategory::orderBy('id')->get();
        foreach ($categories as $key => $category) {
            $category->group = ancestors($category)->first()->id;
            $category->save();
        }
    }
}
