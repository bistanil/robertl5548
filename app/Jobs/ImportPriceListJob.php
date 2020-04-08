<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Libraries\PriceImport;
use Excel;
use Carbon\Carbon;
use File;
use Mail;

class ImportPriceListJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $source;
    protected $email;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($source, $email)
    {
        $this->email = $email;
        $this->source = $source;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        File::put('public/public/public_html/public/files/import/excelPriceListImportLog.txt', 'Import started at '.Carbon::now()."\n");
        Excel::load('public/public/public_html/public/files/import/pricesImport.xlsx', function($reader){            
            $sheets = $reader->get();
            foreach ($sheets->first() as $key => $item) {
                $price = new PriceImport($item, $this->source);
                $price->save();                
            }
        });
        File::append('public/public/public_html/public/files/import/excelPriceListImportLog.txt', 'Import ended at '.Carbon::now()."\n");
        Mail::send('admin.emails.importFinished', [], function ($message) {
                    $message->from(config('mail.defaultEmail'), 'Admin');
                    $message->subject(trans('admin/emails.importFinishedTitle'));
                    $message->to($this->email);
                });
    }
}
