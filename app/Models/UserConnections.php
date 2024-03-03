<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;
use App\Models\UserMeta;

/**
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
    // $selectedUserId is the user to be a member from $connectedToUserId team
    public static function setConnectionData($selectedUserId, $connectedToUserId, $connectionRole, $connectionStatus, $connectionCompanies, $connectionCode)
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
                    'connection_code' => $connectionCode
                ]);
        } else {
            // Insert new record
            return $onboardConnection->table('user_connections')
                ->insert([
                    'user_id' => $selectedUserId,
                    'connected_to' => $connectedToUserId,
                    'connection_role' => $connectionRole,
                    'connection_status' => $connectionStatus,
                    'connection_companies' => $connectionCompanies,
                    'connection_code' => $connectionCode
                ]);
        }

        return false;
    }

    public static function preventUnauthorizedConnection()
    {
        $currentUserId = auth()->id();
        $currentConnectionId = getCurrentConnectionByUserId($currentUserId);

        $connectionStatus = getUserConnectionStatusById($currentUserId, $currentConnectionId);

        if ($connectionStatus != 'active') {
            // This line seems to reset the user's current connection to their own user ID when unauthorized.
            UserMeta::updateUserMeta($currentUserId, 'current_database_connection', $currentUserId);

            // Return an error response when the connection is not active
            return response()->json(['error' => 'Você não possui autorização para acessar esta conexão'], 403);
        }

        // Return a success response when the connection is active
        return response()->json(['authorization' => true], 200);
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

        return $query ?? null;
    }

}
