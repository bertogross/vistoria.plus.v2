<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     */
    protected $commands = [
        \App\Console\Commands\FetchSurveys::class,
    ];


    /**
     * Define the application's command schedule.
     * Example: php artisan make:command FetchSurveys
     * https://laravel.com/docs/10.x/scheduling
     */
    protected function schedule(Schedule $schedule)
    {
        // https://laravel.com/docs/10.x/scheduling
        // Update surveys and tasks to each vpApp(ID) database
        // Usefull if crontab or Kernel schedule is losted
        // to test in linux server terminal: php artisan fetch:surveys
        $schedule->command('fetch:surveys')
            ->dailyAt('01:00')
            ->timezone('America/Sao_Paulo');

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
