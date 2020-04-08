<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Inspire::class,

        Commands\Acl\UpdateAccessControlSections::class,

        Commands\Delete\DeleteNotneededParts::class,
        Commands\Delete\DeleteProductImages::class,
        Commands\Delete\DeleteProducts::class,
        Commands\Delete\RemoveDuplicateProducts::class,

        Commands\CreateProductsFeed::class,
        Commands\GenerateProductsFeed::class,
        
        Commands\Import\ImportCatalogExcel::class,
        Commands\Import\ImportListItemsExcel::class,
        Commands\Import\ImportNewParts::class,
        Commands\Import\ImportOEParts::class,

        Commands\Prices\ImportAutonetCodes::class,
        Commands\Prices\ImportElitCodes::class,
        Commands\Prices\ImportPriceList::class,
        Commands\Prices\ProcessAutonetCodes::class,
        Commands\Prices\ProcessAutototalPriceList::class,
        Commands\Prices\ProcessBennetWebservice::class,
        Commands\Prices\ProcessMateromPricelist::class,

        Commands\TD\DownloadPartPics::class,
        Commands\TD\GenerateEngineSearchCodes::class,
        Commands\TD\GeneratePartCategoriesGroup::class,
        Commands\TD\GenerateProductGroup::class,
        
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        $schedule->command('process-autonet-codes')
                ->weeklyOn(1, '8:00');
        $schedule->command('process-materom-price-list')
                ->dailyAt('01:00');
        $schedule->command('process-autototal-price-list')
                ->dailyAt('03:00');
    }
}
