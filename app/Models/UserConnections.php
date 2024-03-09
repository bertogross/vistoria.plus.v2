<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\UserMeta;
use App\Models\User;

/**
 * Represents the metadata associated with a user.
 */
class UserConnections extends Model
{
    use HasFactory;

    public $timestamps = true;

    // The attributes that are mass assignable.
    protected $fillable = [
        'user_id', 'connected_to', 'connection_role', 'connection_status', 'connection_companies'
    ];


    // Store or update secondary users data
    // $questUserId is the user to be a member from $hostUserId team
    public static function setConnectionData($questUserId, $hostUserId, $questUserRole, $questUserStatus, $questUserCompanies)
    {
        $onboardConnection = DB::connection('vpOnboard');

        $existingData = $onboardConnection->table('user_connections')
            ->where('user_id', $questUserId)
            ->where('connected_to', $hostUserId)
            ->first();

        if ($existingData) {
            // Update existing record
            return $onboardConnection->table('user_connections')
                ->where('id', $existingData->id)
                ->update([
                    'connection_role' => $questUserRole,
                    'connection_status' => $questUserStatus,
                    'connection_companies' => $questUserCompanies,
                ]);
        } else {
            // Insert new record
            return $onboardConnection->table('user_connections')
                ->insert([
                    'user_id' => $questUserId,
                    'connected_to' => $hostUserId,
                    'connection_role' => $questUserRole,
                    'connection_status' => $questUserStatus,
                    'connection_companies' => $questUserCompanies,
                ]);
        }

        return false;
    }

    // Accpet connection to another account
    public static function acceptConnection($request, $questUserId)
    {
        $hostUserId = $request->host_user_id ?? null;
        $questUserParams = $request->quest_user_params ?? null;
        if($hostUserId && $questUserParams){
            $decodeQuestUserParams = $questUserParams ? json_decode($questUserParams, true) : null;
                $guestUserRole = $decodeQuestUserParams->role ?? 4;
                $questUserCompanies = $decodeQuestUserParams->companies ?? [];

            self::setConnectionData($questUserId, $hostUserId, $guestUserRole, 'active', $questUserCompanies);
        }
    }

    // Unset all users connected in current account connections
    // Use status: inactive | revoked
    public static function unsetUsersConnectedOnHostAccount()
    {
        $hostId = auth()->id();

        return DB::connection('vpOnboard')->table('user_connections')
            ->where('connected_to', $hostId)
            ->update([
                'connection_status' => 'inactive'
            ]);

    }

    public static function preventUnauthorizedConnection()
    {
        $currentUserId = auth()->id();
        $currentConnectionId = getCurrentConnectionByUserId($currentUserId);

        $questUserStatus = getUserConnectionStatusById($currentUserId, $currentConnectionId);

        if ($questUserStatus != 'active') {
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
                'connection_companies AS companies',
                'created_at AS since'
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
                'connection_companies AS companies',
                'created_at AS since'
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
                'connection_companies AS companies',
                'created_at AS since'
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
                'connection_companies AS companies',
                'created_at AS since'
            ])
            ->first();

        // Decode the 'companies' JSON field
        if($query){
            $query->companies = json_decode($query->companies, true) ?? null;
        }

        return $query ?? null;
    }

    // Create user database based on vpDefaultSchema
    public static function duplicateAndRenameDefaultSchema($accountId)
    {
        $defaultSchema = 'vpDefaultSchema';
        $newDatabaseName = 'vpApp'.$accountId; // The new name for the duplicated database

        // Check if database exists
        if (DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$newDatabaseName])) {
            return;
        }

        try {
            // Create a new database
            DB::statement("CREATE DATABASE `$newDatabaseName`");

            // Get all tables from the original database
            $tables = DB::select("SHOW TABLES FROM `$defaultSchema`");

            foreach ($tables as $table) {
                foreach ($table as $key => $tableName) {
                    // Duplicate each table into the new database
                    DB::statement("CREATE TABLE `$newDatabaseName`.`$tableName` LIKE `$defaultSchema`.`$tableName`");
                    DB::statement("INSERT INTO `$newDatabaseName`.`$tableName` SELECT * FROM `$defaultSchema`.`$tableName`");
                }
            }
        } catch (\Exception $e) {
            \Log::error('duplicateAndRenameDefaultSchema: ' . $e->getMessage());

            return;
        }

        return true;
    }

    // Switch between connected accounts
    public function changeConnection(Request $request)
    {
        try {
            $currentUserId = auth()->id();

            $connectionId = $request->json('id');

            $questUserStatus = getUserConnectionStatusById($currentUserId, $connectionId);

            if($questUserStatus == 'inactive'){
                UserMeta::updateUserMeta($currentUserId, 'current_database_connection',  $currentUserId);

                return response()->json([
                    'success' => false,
                    'action' => 'infoAlert',
                    'message' => 'Conexão impossibilitada'
                ]);
            }else if($questUserStatus == 'waiting'){
                UserMeta::updateUserMeta($currentUserId, 'current_database_connection',  $currentUserId);

                return response()->json([
                    'success' => false,
                    'action' => 'approvalAlert',
                    'message' => 'Aguardando consentimento'
                ]);
            }

            UserMeta::updateUserMeta($currentUserId, 'current_database_connection',  $connectionId);

            return response()->json([
                'success' => true,
                'message' => 'Alternando conexão...'
            ]);
        } catch (\Exception $e) {
            // Log the exception details for debugging
            \Log::error('Error changing connection: ' . $e->getMessage());

            // Return a response indicating failure
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alternar conexão.'
            ], 500); // 500 Internal Server Error
        }
    }

    // Switch status account connection by quest action
    // When the quest user decides to log out of another account
    public function revokeConnection(Request $request)
    {

        $hostUserId = $request->json('id');

        if(!$hostUserId){
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível executar esta solicitação.'
            ], 500);
        }

        $onboardConnection = DB::connection('vpOnboard');

        $questUserId = auth()->id();

        $existingData = $onboardConnection->table('user_connections')
            ->where('user_id', $questUserId)
            ->where('connected_to', $hostUserId)
            ->first();

        if (!$existingData) {
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível executar esta solicitação.'
            ], 500);
        }

        $currentStatus = $existingData->connection_status;

        if($currentStatus == 'inactive'){
            return response()->json([
                'success' => false,
                'message' => 'Sua conexão foi desativada pelo Host.'
            ], 500);
        }

        if($currentStatus == 'active'){
            $questUserNewStatus = 'revoked';
            $message = 'A conexão foi revogada';
        }
        if($currentStatus == 'revoked'){
            $questUserNewStatus = 'active';
            $message = 'A conexão foi restabelecida';
        }

        // Update quest status record
        $query = $onboardConnection->table('user_connections')
            ->where('id', $existingData->id)
            ->update([
                'connection_status' => $questUserNewStatus,
            ]);

        if(!$query){
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível executar esta solicitação.'
            ], 500);
        }

        // Change current connection immediately
        $currentConnectionId = UserMeta::getUserMeta($questUserId, 'current_database_connection');
        if($currentConnectionId == $hostUserId){
            UserMeta::updateUserMeta($questUserId, 'current_database_connection',  $questUserId);
        }

        return response()->json([
            'success' => true,
            'status' => $questUserNewStatus,
            'message' => $message
            ], 200);

    }
}
