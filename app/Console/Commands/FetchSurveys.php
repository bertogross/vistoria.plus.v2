<?php

namespace App\Console\Commands;

use App\Models\Survey;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FetchSurveys extends Command
{
    protected $signature = 'fetch:surveys';
    protected $description = 'Check survey status and populate user tasks';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the list of user IDs from the 'users' table where 'status' is 1
        // Usefull if crontab or Kernel schedule is losted
        try{
            $userIds = DB::connection('vpOnboard')->table('users')
                        ->where('status', 1)
                        ->pluck('ID');
        } catch (\Exception $e) {
            \Log::error("FetchSurveys console command: " . $e->getMessage());

            return;
        }

        // Check if we have any user IDs to process
        if ($userIds->isNotEmpty()) {
            foreach ($userIds as $databaseId) {
                Survey::populateSurveys($databaseId);
            }
        }
    }

}
