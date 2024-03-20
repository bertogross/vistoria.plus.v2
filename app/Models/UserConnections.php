<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\UserMeta;
use App\Models\User;
use App\Models\Stripe;
use App\Models\SurveyResponse;
use Illuminate\Support\Facades\Crypt;

/**
 * Represents the metadata associated with a user.
 */
class UserConnections extends Model
{
    use HasFactory;

    public $timestamps = true;

    // The attributes that are mass assignable.
    protected $fillable = [
        'guest_id', 'host_id', 'connection_role', 'connection_status', 'connection_companies'
    ];


    // Store or update secondary users data
    // $guestId is the user to be a member from $hostId team
    public static function setConnectionData($guestId, $hostId, $guestUserRole = 3, $guestUserStatus, $guestUserCompanies = null)
    {
        $onboardConnection = DB::connection('vpOnboard');

        // prevent user to invite yourself
        if($guestId == $hostId){
            return;
        }

        $existingData = $onboardConnection->table('user_connections')
            ->where('guest_id', $guestId)
            ->where('host_id', $hostId)
            ->first();

        try {
            if ($existingData) {
                // Update existing record
                $query = $onboardConnection->table('user_connections')
                    ->where('id', $existingData->id)
                    ->update([
                        //'connection_role' => intval($guestUserRole),
                        'connection_status' => $guestUserStatus,
                        //'connection_companies' => $guestUserCompanies,
                    ]);
            } else {
                // Insert new record
                $query = $onboardConnection->table('user_connections')
                    ->insert([
                        'guest_id' => intval($guestId),
                        'host_id' => intval($hostId),
                        //'connection_role' => intval($guestUserRole),
                        'connection_status' => $guestUserStatus,
                        //'connection_companies' => $guestUserCompanies,
                        'created_at' =>now()
                    ]);
            }
        } catch (\Exception $e) {
            \Log::error('setConnectionData error: ' . $e->getMessage());

            return false;
        }

        // Stripe routine to cancel/active user subscription product_type == users
        return Stripe::subscriptionGuestUserAddonUpdate($hostId);
    }

    // Accpet connection to another account
    public static function acceptConnection($request, $guestId)
    {
        /*
        try {
            $decryptedValue = Crypt::decryptString($request->quest_user_params);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Handle the error, e.g., log it or notify the user
            \Log::error("Decryption acceptConnection error: " . $e->getMessage());
            // Optionally, return or throw a custom error
            return response()->json(['error' => 'Decryption failed.'], 500);
        }
        */


        try {
            $hostId = $request->host_user_id ? Crypt::decryptString($request->host_user_id) : null;
            //$guestUserParams = $request->quest_user_params ? Crypt::decryptString($request->quest_user_params) : null;
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Handle the error, for example, log it or return a custom error response
            \Log::error('acceptConnection error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Dados de convite corrompidos.'], 422);
        }

        /*
        if($hostId && $guestUserParams){
            $decodeQuestUserParams = $guestUserParams ? json_decode($guestUserParams, true) : null;
            $guestUserRole = $decodeQuestUserParams['role'] ?? 4;
            $guestUserCompanies = isset($decodeQuestUserParams['companies']) && is_array($decodeQuestUserParams['companies']) ? array_map('intval', $decodeQuestUserParams['companies']) : [];

            self::setConnectionData($guestId, $hostId, $guestUserRole, 'active', $guestUserCompanies);
        }
        */

        if(!$hostId){
            return;
        }

        if($hostId != $guestId){
            $guestUserRole = 3;
            $guestUserCompanies = null;
            self::setConnectionData($guestId, $hostId, $guestUserRole, 'active', $guestUserCompanies);

            $hostUserData = getUserData($hostId);
            $hostEmail = $hostUserData->email;
            $hostName = $hostUserData->name;

            $guestUserData = getUserData($guestId);
            $guestEmail = $guestUserData->email;
            $guestName = $guestUserData->name;

            $message = 'O convite para colaborar com sua conexão outrora enviado para ' . $guestEmail . ' foi aceito. ';

            //appSendEmail($hostEmail, $hostName, 'A Conexão foi Aceita :: [ ' . $guestName . ' ]', $message, 'default');
            // TODO dont send email: add post  notification message ans show in the topbar

        }

    }


    // Create user database based on vpDefaultSchema
    public static function duplicateAndRenameDefaultSchema($hostId)
    {
        $defaultSchema = 'vpDefaultSchema';
        $newDatabaseName = 'vpApp'.$hostId; // The new name for the duplicated database

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

            $guestUserStatus = getUserConnectionStatusById($currentUserId, $connectionId);

            if($guestUserStatus == 'inactive'){
                UserMeta::setUserMeta($currentUserId, 'current_database_connection',  $currentUserId);

                return response()->json([
                    'success' => false,
                    'action' => 'infoAlert',
                    'message' => 'Conexão impossibilitada'
                ]);
            }else if($guestUserStatus == 'waiting'){
                UserMeta::setUserMeta($currentUserId, 'current_database_connection',  $currentUserId);

                return response()->json([
                    'success' => false,
                    'action' => 'approvalAlert',
                    'message' => 'Aguardando consentimento'
                ]);
            }

            UserMeta::setUserMeta($currentUserId, 'current_database_connection',  $connectionId);

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
        $hostId = $request->json('id');

        if(!$hostId){
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível executar esta solicitação.'
            ], 500);
        }

        $onboardConnection = DB::connection('vpOnboard');

        $guestId = auth()->id();

        $hostUserData = getUserData($hostId);
        $hostEmail = $hostUserData->email;
        $hostName = $hostUserData->name;

        $guestUserData = getUserData($guestId);
        $guestEmail = $guestUserData->email;
        $guestName = $guestUserData->name;

        $existingData = $onboardConnection->table('user_connections')
            ->where('guest_id', $guestId)
            ->where('host_id', $hostId)
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
            $guestUserNewStatus = 'revoked';
            $message = 'A conexão foi revogada';

            $mailMessage = $guestName . ' Revogou a conexão com seu ' . appName();

            //appSendEmail($hostEmail, $hostName, 'A Conexão foi Revogada :: [ ' . $guestName . ' ]', $mailMessage, 'default');
            // TODO dont send email: add post  notification message ans show in the topbar
        }
        if($currentStatus == 'revoked'){
            $guestUserNewStatus = 'active';
            $message = 'A conexão foi restabelecida';

            $mailMessage = $guestName . ' Restabeleceu a conexão com seu ' . appName();

            //appSendEmail($hostEmail, $hostName, 'A Conexão foi Restabelecida :: [ ' . $guestName . ' ]', $mailMessage, 'default');
            // TODO dont send email: add post  notification message ans show in the topbar
        }

        // Update quest status record
        $query = $onboardConnection->table('user_connections')
            ->where('id', $existingData->id)
            ->update([
                'connection_status' => $guestUserNewStatus,
            ]);

        if(!$query){
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível executar esta solicitação.'
            ], 500);
        }

        // Stripe routine to cancel/active user subscription product_type == users
        Stripe::subscriptionGuestUserAddonUpdate($hostId);

        // Change current connection immediately
        $currentConnectionId = UserMeta::getUserMeta($guestId, 'current_database_connection');
        if($currentConnectionId == $hostId){
            UserMeta::setUserMeta($guestId, 'current_database_connection',  $guestId);
        }

        return response()->json([
                'success' => true,
                'status' => $guestUserNewStatus,
                'message' => $message
            ], 200);

    }

    // Delete Guest
    // Check if are posts. If not, delete. If has, mantain.
    public function deleteGuestFromHostId($guestId, $hostId)
    {
        $onboardConnection = DB::connection('vpOnboard');

        $error = response()->json([
            'success' => false,
            'message' => 'Não foi possível remover esta conexão.'
        ], 400);

        $guestUser = User::find($guestId);
        if(!$guestUser){
            return $error;
        }

        $hostUser = User::find($hostId);
        if(!$hostUser){
            return $error;
        }

        // check if $questId has the $hostId connection
        $getHostConnection = self::getGuestDataFromConnectedHostId($guestId, $hostId);
        if(!$getHostConnection){
            return $error;
        }

        // Check if $guest have posts
        $postsExixts = SurveyAssignments::where('surveyor_id', $guestId)
            ->count();
        if($postsExixts){
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível remover esta conexão pois este usuário possui registros.'
            ], 400);
        }else{
            // Delete user
            try {
                $deleted = $query = $onboardConnection->table('user_connections')
                    ->where('quest_id', $questId)
                    ->where('host_id', $hostId)
                    ->delete();

                if ($deleted) {
                    // return response()->json(['success' => true, 'message' => 'Conexão removida.'], 200);
                    return true;
                } else {
                    return $error;
                }
            } catch (\Exception $e) {
                \Log::error('Error to delete connection: ' . $e->getMessage());

                return response()->json(['success' => false, 'message' => 'Um erro ocorreu ao tentar remover esta conexão: ' . $e->getMessage()]);
            }
        }
    }

    // Unset all users connected in current account connections
    // Usefull when user cancel your subscription
    public static function unsetGuestsConnectedOnHost()
    {
        $hostId = auth()->id();

        $query = DB::connection('vpOnboard')->table('user_connections')
            ->where('host_id', $hostId)
            ->where('connection_status', 'active')
            ->update([
                'connection_status' => 'inactive'
            ]);

        if($query){
            // Stripe routine to cancel/active user subscription product_type == users
            return Stripe::subscriptionGuestUserAddonUpdate($hostId);
        }

    }

    public static function preventUnauthorizedConnection()
    {
        $currentUserId = auth()->id();
        $currentConnectionId = getCurrentConnectionByUserId($currentUserId);

        $guestUserStatus = getUserConnectionStatusById($currentUserId, $currentConnectionId);

        if ($guestUserStatus != 'active') {
            // This line seems to reset the user's current connection to their own user ID when unauthorized.
            UserMeta::setUserMeta($currentUserId, 'current_database_connection', $currentUserId);

            // Return an error response when the connection is not active
            return response()->json(['error' => 'Você não possui autorização para acessar esta conexão'], 403);
        }

        // Return a success response when the connection is active
        return response()->json(['authorization' => true], 200);
    }

    //  Get Guests from HostId
    public static function getGuestConnections($hostId = null)
    {
        if(!$hostId){
            $hostId = auth()->id();
        }
        $query = DB::connection('vpOnboard')->table('user_connections')
            ->where('host_id', $hostId)
            ->select([
                'guest_id AS user_id',
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

    //  Get Hosts from GuestId
    public static function getHostConnections($guestId = null)
    {
        if(!$guestId){
            $guestId = auth()->id();
        }
        $query = DB::connection('vpOnboard')->table('user_connections')
            ->where('guest_id', $guestId)
            ->select([
                'host_id AS user_id',
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

    public static function getGuestIdsConnectedOnHostId($hostId = null)
    {
        if(!$hostId){
            $hostId = auth()->id();
        }
        return DB::connection('vpOnboard')->table('user_connections')
            ->where('host_id', $hostId)
            ->pluck('guest_id')
            ->toArray();
    }

    public static function getGuestDataFromConnectedHostId($guestId, $hostId)
    {
        $query = DB::connection('vpOnboard')->table('user_connections')
            ->where('guest_id', $guestId)
            ->where('host_id', $hostId)
            ->select([
                'guest_id',
                'host_id',
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

    // Check if quest revoke connection or host turn user connection off
    public static function connectedAccountData($guestId)
    {
        $config = config("database.connections.vpAppTemplate");
        $connectionId = onlyNumber($config['database']);
        $connectedAccount = self::getGuestDataFromConnectedHostId($guestId, $connectionId);

        return $connectedAccount ?? null;
    }

    public function acceptOrDeclineConnection(Request $request)
    {
        $error = response()->json([
            'success' => false,
            'message' => 'Não foi possível estabelecer uma conexão.'
        ], 400);

        if(!auth()->check()){
            return $error;
        }

        $guestId = auth()->id();

        $hostId = $request->json('hostId');
        $decision = $request->json('decision');

        $hostUser = User::find($hostId);
        if(!$hostUser){
            return $error;
        }

        // check if $questId has the $hostId invitation
        $getHostConnections = self::getHostConnections($guestId);
        $firstConnection = $getHostConnections->first();
        if(!$firstConnection){
            return $error;
        }

        if($hostId && in_array($decision, ['accept', 'decline'])){

            if($decision == 'decline'){
                self::deleteGuestFromHostId($guestId, $hostId);

                // Send message notification to host
                $hostUserData = getUserData($hostId);
                $hostEmail = $hostUserData->email;
                $hostName = $hostUserData->name;

                $guestUserData = getUserData($guestId);
                $guestEmail = $guestUserData->email;
                $guestName = $guestUserData->name;

                $message = "O convite que você enviou a $guestEmail para colaborar em sua conexão não foi aceito. <br><br>Por favor, verifique se o endereço de e-mail está correto. Se você acredita que houve um equívoco, entre em contato diretamente com a pessoa. Se necessário, envie um novo convite.";

                appSendEmail($hostEmail, $hostName, 'A Conexão foi Recusada :: [ ' . $guestName . ' ]', $message, 'default');
                // TODO dont send email: add post  notification message ans show in the topbar

                return response()->json([
                    'success' => false,
                    'message' => 'O convite para conexão foi recusado.'
                ], 200);
            }else{
                self::setConnectionData($guestId, $hostId, 3, 'active');

                return response()->json([
                    'success' => true,
                    'message' => 'Conexão estabelecida.'
                ], 200);
            }

        }else{
            return $error;
        }

    }

}
