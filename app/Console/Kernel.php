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
      $schedule->job(new ProcessImportXmlToDb, 'import')
                ->withoutOverlapping()
                ->pingBefore('https://api.restoreca.ru/import/products')
                ->timezone('Europe/Samara')
                ->dailyAt('01:57');
      $schedule->job(new ProcessImportCategories, 'import')
                ->withoutOverlapping()
                ->timezone('Europe/Samara')
                ->dailyAt('02:00');
      $schedule->job(new ProcessImportProducts, 'import')
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
