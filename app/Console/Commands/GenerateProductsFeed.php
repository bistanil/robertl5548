<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateProductsFeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generateproductsfeed {feed} {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate products feed file for given feed type';

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
       $feed = app('App\Http\Libraries\Feeds\\'.$this->argument('feed'), [$this->argument('email')]);        
       $feed->generateFeed();
    }
}