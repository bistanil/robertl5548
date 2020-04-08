<?php

namespace App\Console\Commands\Import;

use Illuminate\Console\Command;
use App\Models\CatalogList;
use App\Models\CatalogListItem;
use Excel;

class ImportListItemsExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-list-items-excel {listId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import list items from excel file';

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
        $list = CatalogList::find($this->argument('listId'));  
        Excel::load('public_html/public/files/import/listItemsImport.xlsx', function($reader) use ($list) {            
           $sheets = $reader->get();
            foreach ($sheets as $sheet) {
                foreach ($sheet as $key => $item) {
                    if ($item->value != '' )
                    {  
                        $listItem = new CatalogListItem();
                        $listItem->active = 'active';
                        $listItem->value = $item->value;
                        $listItem->list_id = $list->id;                            
                        $listItem->save();
                    }                
                }                
            } 
        });
    }
}
