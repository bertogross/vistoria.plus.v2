<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OnboardController extends Controller
{

    // Get the password of a user from other vpApp databases
    public static function getPasswordFromOtherDatabases($email)
    {
        // Get the list of other vpApp databases from vpOnboard
        $otherDatabases = OnboardController::getOtherDatabases($email);

        foreach ($otherDatabases as $data) {
            // Skip the current database
            if ($data['database'] == config('database.connections.vpAppTemplate.database')) {
                continue;
            }

            $databaseExists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$data['database']]);
            if (!$databaseExists) {
                return null;
            }

            // Set the database connection configuration for the other database
            config([
                'database.connections.otherDatabase' => [
                    'driver' => 'mysql',
                    'host' => env('DB_HOST'),
                    'port' => env('DB_PORT'),
                    'database' => $data['database'],
                    'username' => env('DB_USERNAME'),
                    'password' => env('DB_PASSWORD'),
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'strict' => true,
                    'engine' => null,
                ],
            ]);

            // Check if the user exists in the other database
            $user = DB::connection('otherDatabase')
                ->table('users')
                ->where('email', $email)
                ->where('status', 1)
                ->first();

            if ($user) {
                // Disconnect from the other database
                DB::disconnect('otherDatabase');

                // Return the user's password
                return $user->password;
            }

            // Disconnect from the other database
            DB::disconnect('otherDatabase');
        }

        // User not found in other databases
        return null;
    }

    // Update/Create subusers in vpOnboard
    public static function updateOrCreateSubUser($email)
    {
        $databaseConnection = config('database.connections.vpAppTemplate.database');

        // Get the ID of the current database connection. It is a helper function
        $databaseId = extractDatabaseId($databaseConnection);

        $onboardConnection = DB::connection('vpOnboard');

        $subuserExist = $onboardConnection->table('app_subusers')
            ->where('sub_user_email', $email)
            ->select('app_IDs')
            ->first();

        if ($subuserExist && !empty($subuserExist->app_IDs)) {
            $appIds = json_decode($subuserExist->app_IDs, true);

            if (!in_array($databaseId, $appIds)) {
                $appIds[] = $databaseId;

                $onboardConnection->table('app_subusers')
                    ->where('sub_user_email', $email)
                    ->update(['app_IDs' => json_encode($appIds)]);
            }
        } else {
            // Create a new record with the given email and app_ID
            $appIds = [$databaseId];
            $onboardConnection->table('app_subusers')
                ->insert([
                    'sub_user_email' => $email,
                    'app_IDs' => json_encode($appIds),
                ]);
        }
    }

    // Get the list of other vpApp databases from vpOnboard
    public static function getOtherDatabases($email)
    {
        if (!$email) {
            return null;
        }

        $OnboardConnection = DB::connection('vpOnboard');

        // Initialize an array to store other databases
        $otherDatabases = [];

        // Get the list of database names from app_users
        $appUsersTable = $OnboardConnection->table('app_users')
            ->where('user_email', $email)
            ->get()
            ->toArray();

        if($appUsersTable){
            foreach ($appUsersTable as $appId) {
                $databaseId = $appId->ID;
                $environmentName = $appId->user_display_name;

                $otherDatabases[] = [
                    'database' => 'vpApp' . $databaseId,
                    'customer' => $environmentName ?? ''
                ];
            }
        }

        // Get the list of app_IDs from app_subusers where sub_user_email is the given email
        $appSubusersTable = $OnboardConnection->table('app_subusers')
            ->where('sub_user_email', $email)
            ->select('app_IDs')
            ->first();

        if ($appSubusersTable && !empty($appSubusersTable->app_IDs)) {
            $appIDs = json_decode($appSubusersTable->app_IDs, true);

            if (is_array($appIDs)) {
                foreach ($appIDs as $key => $databaseId) {
                    $environmentName = OnboardController::getCustomerNameByDatabaseId($databaseId);

                    $otherDatabases[] = [
                        'database' => 'vpApp' . $databaseId,
                        'customer' => $environmentName ?? ''
                    ];
                }
            }
        }

        return array_filter($otherDatabases);
    }


    public static function getCustomerNameByDatabaseId($databaseId)
    {
        if(!$databaseId){
            return;
        }

        $databaseId = onlyNumber($databaseId);
        $databaseId = intval($databaseId);

        $OnboardConnection = DB::connection('vpOnboard');

        // Get the list of database names from app_users
        $appUsersTable = $OnboardConnection->table('app_users')
            ->where('ID', $databaseId)
            ->first();

        return $appUsersTable->user_display_name;
    }

}
