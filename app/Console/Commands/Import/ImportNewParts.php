<?php

namespace App\Console\Commands\Import;

use Illuminate\Console\Command;
use App\Http\Libraries\CopyPart;
use App\Models\Supplier;
use Excel;
use Carbon\Carbon;
use File;
use Mail;

class ImportNewParts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-new-parts {supplier_id} {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import parts that are not in tecdoc: original parts or aftermarket parts not listed in tecdoc';

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
        //  
        $supplier = Supplier::find($this->argument('supplier_id'));      
        File::put('public_html/public/files/import/excelPartsImportLog.txt', 'Import started at '.Carbon::now()."\n");
        Excel::load('public_html/public/files/import/partsImport.xlsx', function($reader) use ($supplier){            
            $sheets = $reader->get();
            foreach ($sheets as $sheet) {
                foreach ($sheet as $key => $result) {
                    if ($key>0)
                    {                    
                        $result->type = 'new-am-part';
                        $copyPart = new CopyPart($supplier);
                        $copyPart->part($result);
                    }
                }                
            }
        });
        File::append('public_html/public/files/import/excelPartsImportLog.txt', 'Import ended at '.Carbon::now()."\n");
        Mail::send('admin.emails.importFinished', [], function ($message) {
                    $message->from(config('mail.defaultEmail'), 'Admin');
                    $message->subject(trans('admin/emails.importFinishedTitle'));
                    $message->to($this->argument('email'));
                });
    }
}
