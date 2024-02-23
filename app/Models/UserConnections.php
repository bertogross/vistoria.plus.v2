<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;

/**
 * UserMeta Model
 *
 * Represents the metadata associated with a user.
 */
class UserConnections extends Model
{
    use HasFactory;

    public $timestamps = true;

    // The attributes that are mass assignable.
    protected $fillable = [
        'user_id', 'connected_to', 'connection_status', 'connection_companies'
    ];


    // Store or update secondary users data
    // $selectedUserId = the user selecyted to be a member from $connectedToUserId team
    public static function setConnectionData($selectedUserId, $connectedToUserId, $connectionRole, $connectionStatus, $connectionCompanies)
    {
        $onboardConnection = DB::connection('vpOnboard');

        $existingData = $onboardConnection->table('user_connections')
            ->where('user_id', $selectedUserId)
            ->where('connected_to', $connectedToUserId)
            ->first();

        if ($existingData) {
            // Update existing record
            return $onboardConnection->table('user_connections')
                ->where('id', $existingData->id)
                ->update([
                    'connection_role' => $connectionRole,
                    'connection_status' => $connectionStatus,
                    'connection_companies' => $connectionCompanies,
                ]);
        } else {
            // Insert new record
            return $onboardConnection->table('user_connections')
                ->insert([
                    'user_id' => $selectedUserId,
                    'connected_to' => $connectedToUserId,
                    'connection_role' => $connectionRole,
                    'connection_status' => $connectionStatus,
                    'connection_companies' => $connectionCompanies
                ]);
        }

        return false;
    }

    public static function getUsersDataConnectedOnAccountId($accountId)
    {
        $query = DB::connection('vpOnboard')->table('user_connections')
            ->where('connected_to', $accountId)
            ->select([
                'user_id',
                'connection_role AS role',
                'connection_status AS status',
                'connection_companies AS companies'
            ])
            ->get();

        // Decode the 'companies' JSON field
        foreach ($query as $connection) {
            $connection->companies = $connection->companies ? json_decode($connection->companies, true) : null;
        }

        return $query;
    }

    public static function getUserIdsConnectedOnAccountId($accountId)
    {
        return DB::connection('vpOnboard')->table('user_connections')
            ->where('connected_to', $accountId)
            ->pluck('user_id')
            ->toArray();
    }

    public static function getUsersDataConnectedOnMyAccount()
    {
        $accountId = auth()->id();

        $query = DB::connection('vpOnboard')->table('user_connections')
            ->where('connected_to', $accountId)
            ->select([
                'user_id',
                'connection_role AS role',
                'connection_status AS status',
                'connection_companies AS companies'
            ])
            ->get();

        // Decode the 'companies' JSON field
        foreach ($query as $connection) {
            $connection->companies = $connection->companies ? json_decode($connection->companies, true) : null;
        }

        return $query;
    }

    public static function getUserIdsConnectedOnMyAccount()
    {
        $accountId = auth()->id();

        return DB::connection('vpOnboard')->table('user_connections')
            ->where('connected_to', $accountId)
            ->pluck('user_id')
            ->toArray();
    }

    public static function getUsersDataFromMyConnections()
    {
        $userId = auth()->id();

        $query = DB::connection('vpOnboard')->table('user_connections')
            ->where('user_id', $userId)
            ->select([
                'connected_to',
                'connection_role AS role',
                'connection_status AS status',
                'connection_companies AS companies'
            ])
            ->get();

        // Decode the 'companies' JSON field
        foreach ($query as $connection) {
            $connection->companies = $connection->companies ? json_decode($connection->companies, true) : null;
        }

        return $query;
    }

    public static function getUserDataFromConnectedAccountId($userId, $accountId)
    {
        $query = DB::connection('vpOnboard')->table('user_connections')
            ->where('user_id', $userId)
            ->where('connected_to', $accountId)
            ->select([
                'connection_role AS role',
                'connection_status AS status',
                'connection_companies AS companies'
            ])
            ->first();

        // Decode the 'companies' JSON field
        if($query){
            $query->companies = json_decode($query->companies, true) ?? null;
        }

        /*
        if( is_null($query) ){
            $getActiveCompanies = getActiveCompanies();
            $getActiveCompanies = !$getActiveCompanies ? [1] : array_column($getActiveCompanies, 'id');

            return (object) array(
                'status' => 'active',
                'role' => 1,
                'companies' => $getActiveCompanies
            );
        }*/

        return $query ?? null;
    }

}
