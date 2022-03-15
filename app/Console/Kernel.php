<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\ProcessXmlToDbJob;
use App\Jobs\ImportProductCategoriesJob;
use App\Jobs\ImportProductsJob;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
      $schedule->job(new ProcessXmlToDbJob, 'import')
                ->withoutOverlapping()
                ->pingBefore('https://api.restoreca.ru/import/products')
                ->timezone('Europe/Samara')
                ->dailyAt('01:57');
      $schedule->job(new ImportProductCategoriesJob, 'import')
                ->withoutOverlapping()
                ->timezone('Europe/Samara')
                ->dailyAt('02:00');
      $schedule->job(new ImportProductsJob, 'import')
                ->withoutOverlapping()
                ->timezone('Europe/Samara')
                ->dailyAt('02:05');

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
