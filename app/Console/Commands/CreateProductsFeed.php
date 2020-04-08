<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FeedProduct;
use App\Models\Feed;

class CreateProductsFeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-products-feed {email} {id}';

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
        $id = $this->argument('id');
        $request = FeedProduct::find($id);
        $request = $request->toArray();
        $feed = Feed::find($request['feed_id']);
        if ($request['type'] == 'catalogs') $feed = app()->makeWith('App\Http\Libraries\Feeds\\'.$feed->class_name, ['request' => $request]);
        else $feed = app()->makeWith('App\Http\Libraries\Feeds\TD'.$feed->class_name, ['request' => $request]);
        $feed->generateFeed();
        /*Mail::send('admin.emails.feedFinished', ['url' => $url], function ($message) use ($url) {
                $message->from(config('mail.defaultEmail'), 'Admin');
                $message->subject(trans('admin/emails.feedFinished'));
                $message->to($this->argument('email'));
            }); */
    }
}