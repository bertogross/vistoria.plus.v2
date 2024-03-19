<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class DynamicDatabaseServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->setDynamicDatabaseConnection();
    }

    public static function setDynamicDatabaseConnection()
    {
        if (auth()->check()) {
            $userId = auth()->id();
            $currentConnectionId = getCurrentConnectionByUserId($userId);

            if ($currentConnectionId) {

                $databaseName = 'vpApp' . $currentConnectionId;

                if (DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$databaseName])) {
                    config(['database.connections.vpAppTemplate.database' => $databaseName]);
                }
            }
        }


    }
}
