<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\UserMeta;
use App\Models\UserConnections;
use App\Models\Survey;
use App\Models\SurveyTerms;
use App\Models\SurveyResponseTopic;
use App\Models\SurveyResponse;
use App\Models\SurveyTemplates;
use App\Models\SurveyAssignments;
use App\Models\Stripe;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\PostmarkappController;
use Illuminate\Support\Facades\Storage;

// Set the locale to Brazilian Portuguese
Carbon::setLocale('pt_BR');

/**
 * Returns the name of the application.
 *
 * @return string The name of the application.
 */
if (!function_exists('appName')) {
    function appName(){
        return env('APP_NAME');
    }
}

/**
 * Returns the description of the application.
 *
 * @return string The description of the application.
 */
if (!function_exists('appDescription')) {
    function appDescription(){
        return 'Garantindo Excelência Operacional';
    }
}

/**
 * Sends an email using Postmark API.
 * usage example  : appSendEmail('bertogross@gmail.com', 'customer name here', 'subject here', 'content here with <strong>strong</strong>', 'welcome');
 *
 * @param string $to The recipient's email address.
 * @param string $name The recipient's name.
 * @param string $subject The subject of the email.
 * @param string $content The content of the email.
 * @param string $template (Optional) The template to use for the email. Default is 'default'.
 * @return bool True if the email was sent successfully, false otherwise.
 */
if (!function_exists('appSendEmail')) {
    function appSendEmail($to, $name, $subject, $content, $template = 'default'){
        try{
            return PostmarkappController::sendEmail($to, $name, $subject, $content, $template);
        } catch (\Exception $e) {
            \Log::error('appSendEmail: ' . $e->getMessage());

            return false;
        }
    }
}

/*if (!function_exists('setDatabaseConnection')) {
    function setDatabaseConnection(){
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
}*/

// Get all users with status = 1, ordered by name
/*if (!function_exists('getUsers')) {
    function getUsers() {
        $getUsers = DB::connection('vpAppTemplate')
            ->table('users')
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        $getUsers = $getUsers ?? null;
        return is_string($getUsers) ? json_decode($getUsers, true) : $getUsers;
    }
}*/

/**
 * Retrieves user models for the current account and connected users.
 *
 * @return \App\Models\User[] The collection of user models.
 */
if( !function_exists('getUsers') ){
    function getUsers() {
        $userIds = [];

        $currentAccountId = auth()->id();
        $userIds[] = $currentAccountId;

        $currentConnectionId = getCurrentConnectionByUserId($currentAccountId);
        $userIds[] = $currentConnectionId;

        // Fetch connected user data based on the host: currentConnectionId
        $getGuestIdsConnectedOnHostId = UserConnections::getGuestIdsConnectedOnHostId($currentConnectionId);

        // If there are connected users, add their IDs to the array
        if ($getGuestIdsConnectedOnHostId) {
            foreach ($getGuestIdsConnectedOnHostId as $userId) {
                $userIds[] = $userId;
            }
        }

        $userIds = array_filter($userIds);

        // Remove duplicates and convert to integers (if necessary)
        $userIds = array_map('intval', array_unique($userIds));

        // Fetch user models for all IDs collected
        $users = User::findMany($userIds);

        return $users;
    }
}

/**
 * Retrieves the role of a user by their ID and account ID.
 *
 * @param int $userId The ID of the user.
 * @param int $accountId The ID of the account.
 * @return int|null The role of the user.
 */
if (!function_exists('getUserRoleById')) {
    function getUserRoleById($userId, $accountId){

        if($accountId == auth()->id()){
            $userRole = 1;
        }else{
            $connection = UserConnections::getGuestDataFromConnectedHostId($userId, $accountId);
            $userRole = isset($connection->role) ? $connection->role : null;
        }

        return $userRole;
    }
}

/**
 * Retrieves users by their role.
 *
 * @param int $role The role of the users.
 * @return \Illuminate\Support\Collection|null The collection of users with the specified role, or null if no role is provided.
 */
if (!function_exists('getUsersByRole')) {
    function getUsersByRole($role){
        if($role){
            $getUsersByRole = DB::connection('vpAppTemplate')
                ->table('users')
                ->where('role', $role)
                ->where('status', 1) // Assuming you want to get only active users
                ->orderBy('name')
                ->get();

            $getUsersByRole = $getUsersByRole ?? null;
            return is_string($getUsersByRole) ? json_decode($getUsersByRole, true) : $getUsersByRole;
        }

        return null;
    }
}

/**
 * Retrieves the connection status of a user by their ID and account ID.
 *
 * @param int $userId The ID of the user.
 * @param int $accountId The ID of the account.
 * @return string|null The connection status of the user.
 */
if (!function_exists('getUserConnectionStatusById')) {
    function getUserConnectionStatusById($userId, $accountId){

        if($accountId == auth()->id()){
            $userStatus = 'active';
        }else{
            $connection = UserConnections::getGuestDataFromConnectedHostId($userId, $accountId);
            $userStatus = isset($connection->status) ? $connection->status : null;
        }

        return $userStatus;
    }
}

/**
 * Retrieves user data from vpOnboard table.
 *
 * @param int|null $userId The ID of the user. If null, retrieves data for the authenticated user.
 * @return mixed The user data.
 */
if( !function_exists('getUserData') ){
    function getUserData($userId = null) {
        if(!$userId){
            $userId = auth()->id();
        }

        return DB::connection('vpOnboard')->table('users')
            ->where('id', intval($userId))
            ->first();
    }
}

/**
 * Retrieves the disk quota for a user.
 *
 * @param int|null $connectionId The ID of the user. If null, retrieves data for the authenticated user.
 * @return int The disk quota for the user.
 */
if( !function_exists('getDiskQuota') ){
    function getDiskQuota($connectionId = null) {
        if(!$connectionId){
            $connectionId = auth()->id();
        }

        $getUserData = getUserData($connectionId);

        $diskQuota = $getUserData->disk_quota;
    }
}

/**
 * Checks the free disk space for a user.
 *
 * @param int|null $connectionId The ID of the user. If null, retrieves data for the authenticated user.
 * @return array An array containing disk space information.
 */
if( !function_exists('checkFreeDiskSpace') ){
    function checkFreeDiskSpace($connectionId = null) {
        $currentUserId = auth()->id();

        if(!$connectionId){
            $connectionId = getCurrentConnectionByUserId($currentUserId);
        }

        $directory = 'vpApp'.$connectionId.'/attachments'; // Base directory

        $getUserData = getUserData($connectionId); // Ensure this function returns an object with a disk_quota property

        $diskQuotaGB = $getUserData->disk_quota; // Assuming disk_quota is in GB

        // Convert $diskQuota from GB to Bytes
        // 1 GB = 1,073,741,824 bytes
        $diskQuotaBytes = $diskQuotaGB * 1073741824;

        $files = collect(Storage::disk('public')->allFiles($directory));

        // Calculate total usage in bytes
        $totalUsageBytes = $files->reduce(function ($carry, $file) {
            return $carry + Storage::disk('public')->size($file);
        }, 0);

        // Convert total usage to gigabytes (GB) for easier understanding
        $totalUsageGB = $totalUsageBytes / 1073741824;

        // Calculate the available space in bytes
        $availableSpaceBytes = $diskQuotaBytes - $totalUsageBytes;

        // Convert the available space to gigabytes (GB) for easier understanding
        $availableSpaceGB = $availableSpaceBytes / 1073741824;

        // Calculate the percentage of disk quota used
        $percentageUsed = ($totalUsageBytes / $diskQuotaBytes) * 100;

        // Return the calculated values, including available space
        return [
            'diskQuotaGB' => $diskQuotaGB,
            'totalUsageGB' => number_format($totalUsageGB, 5),
            'availableSpaceGB' => number_format($availableSpaceGB, 5),
            'percentageUsed' => number_format($percentageUsed, 5)
        ];
    }
}

/**
 * Retrieves the user's avatar.
 *
 * @param int|null $userId The ID of the user. If null, retrieves data for the authenticated user.
 * @return string The URL of the user's avatar image.
 */
if( !function_exists('getUserAvatar') ){
    function getUserAvatar($userId = null) {
        $user = getUserData($userId);

        $avatar = URL::asset('build/images/users/user-dummy-img.jpg');

        if ($user) {
            $path = $user->avatar ? 'storage/' . $user->avatar : null;

            if($path && @getimagesize($path)){
                return URL::asset('storage/' . $user->avatar);
            }
        }

        return $avatar;
    }
}

/**
 * Checks if the user's avatar exists.
 *
 * @param string|null $avatar The avatar image path.
 * @return string The URL of the user's avatar image.
 */
if( !function_exists('checkUserAvatar') ){
    function checkUserAvatar($avatar = null) {
        $path = $avatar ? 'storage/' . $avatar : null;

        if($path && @getimagesize($path)){
            return URL::asset('storage/' . $avatar);
        }else{
            return URL::asset('build/images/users/user-dummy-img.jpg');
        }
    }
}

/**
 * Generates a snippet for the user's avatar or initials.
 *
 * @param string|null $avatar The avatar image path.
 * @param string|null $name The user's name.
 * @param string $class The CSS class for the avatar.
 * @param string|bool $style The CSS style for the avatar.
 * @return string The HTML snippet for the avatar.
 */
if( !function_exists('snippetAvatar') ){
    function snippetAvatar($avatar = null, $name = null, $class = 'rounded-circle avatar-xxs', $style = false) {
        $path = $avatar ? 'storage/' . $avatar : null;

        if($path && @getimagesize($path)){
            $result = URL::asset('storage/' . $avatar);
            return '<img src="'.$result.'" alt="Avatar" class="'.$class.'" loading="lazy">';
        }else{
            $letter = $name ? substr($name, 0, 1) : '?';

            switch ($letter) {
                case 'A':
                    $background = "#FF5733";
                    $text = "#FFFFFF";
                    break;
                case 'B':
                    $background = "#33FF57";
                    $text = "#000000";
                    break;
                case 'C':
                    $background = "#3357FF";
                    $text = "#FFFFFF";
                    break;
                case 'D':
                    $background = "#F33FF5";
                    $text = "#FFFFFF";
                    break;
                case 'E':
                    $background = "#3CFF33";
                    $text = "#000000";
                    break;
                case 'F':
                    $background = "#33F3FF";
                    $text = "#000000";
                    break;
                case 'G':
                    $background = "#FF5733";
                    $text = "#FFFFFF";
                    break;
                case 'H':
                    $background = "#8E44AD";
                    $text = "#FFFFFF";
                    break;
                case 'I':
                    $background = "#3498DB";
                    $text = "#FFFFFF";
                    break;
                case 'J':
                    $background = "#1ABC9C";
                    $text = "#000000";
                    break;
                case 'K':
                    $background = "#2ECC71";
                    $text = "#000000";
                    break;
                case 'L':
                    $background = "#F1C40F";
                    $text = "#000000";
                    break;
                case 'M':
                    $background = "#E67E22";
                    $text = "#FFFFFF";
                    break;
                case 'N':
                    $background = "#E74C3C";
                    $text = "#FFFFFF";
                    break;
                case 'O':
                    $background = "#95A5A6";
                    $text = "#000000";
                    break;
                case 'P':
                    $background = "#F39C12";
                    $text = "#000000";
                    break;
                case 'Q':
                    $background = "#D35400";
                    $text = "#FFFFFF";
                    break;
                case 'R':
                    $background = "#C0392B";
                    $text = "#FFFFFF";
                    break;
                case 'S':
                    $background = "#BDC3C7";
                    $text = "#000000";
                    break;
                case 'T':
                    $background = "#7F8C8D";
                    $text = "#FFFFFF";
                    break;
                case 'U':
                    $background = "#563CFF";
                    $text = "#FFFFFF";
                    break;
                case 'V':
                    $background = "#605A4C";
                    $text = "#FFFFFF";
                    break;
                case 'W':
                    $background = "#AF7AC5";
                    $text = "#FFFFFF";
                    break;
                case 'X':
                    $background = "#1B4F72";
                    $text = "#FFFFFF";
                    break;
                case 'Y':
                    $background = "#1ABC9C";
                    $text = "#000000";
                    break;
                case 'Z':
                    $background = "#F7DC6F";
                    $text = "#000000";
                    break;
                default:
                    $background = "#563CFF";
                    $text = "#FFFFFF";
                    break;
            }

            return '<span class="fs-15 '.$class.' text-uppercase text-white d-inline-block text-center font-monospace align-middle overflow-hidden" loading="lazy" style=" background-color:'.$background.'; color: '.$text.';  '.$style.'">'.$letter.'</span>';
        }
    }
}

/**
 * Retrieves the user's cover image URL.
 *
 * @param int|null $userId The ID of the user.
 * @return string The URL of the user's cover image.
 */
if( !function_exists('getUserCover') ){
    function getUserCover($userId = null) {
        $user = getUserData($userId);

        $cover = URL::asset('build/images/small/img-9.jpg');

        if ($user) {
            $path = $user->cover ? 'storage/' . $user->cover : null;

            if($path && @getimagesize($path)){
                return URL::asset('storage/' . $user->cover);
            }
        }

        return $cover;
    }
}

/**
 * Checks and retrieves the user's cover image URL.
 *
 * @param string|null $cover The cover image path.
 * @return string The URL of the user's cover image.
 */
if( !function_exists('checkUserCover') ){
    function checkUserCover($cover = null) {
        $path = $cover ? 'storage/' . $cover : null;

        if($path && @getimagesize($path)){
            return URL::asset('storage/' . $cover);
        }else{
            return URL::asset('build/images/small/img-9.jpg');
        }
    }
}

/**
 * Retrieve a user's meta value based on the given key.
 *
 * @param int $userId The user ID.
 * @param string $key The metadata key.
 * @return mixed The value of the user metadata.
 */
if (!function_exists('getUserMeta')) {
    function getUserMeta($userId, $key) {
        return UserMeta::getUserMeta($userId, $key);
    }
}

/**
 * Retrieves the current connection ID associated with the given user ID.
 *
 * @param int|bool $userId The user ID. Defaults to the authenticated user's ID.
 * @return int The current connection ID.
 */
if (!function_exists('getCurrentConnectionByUserId')) {
    function getCurrentConnectionByUserId($userId = false) {
        if(!$userId){
            $userId = auth()->id();
        }
        $connectionId = UserMeta::getUserMeta($userId, 'current_database_connection');

        return $connectionId ? intval($connectionId) : $userId;
    }
}

/**
 * Retrieves the guest connections.
 *
 * @return Illuminate\Support\Collection The guest connections.
 */
if (!function_exists('getGuestConnections')) {
    function getGuestConnections() {
        return UserConnections::getGuestConnections();
    }
}

/**
 * Retrieves the host connections.
 *
 * @return Illuminate\Support\Collection The host connections.
 */
if (!function_exists('getHostConnections')) {
    function getHostConnections() {
        return UserConnections::getHostConnections();
    }
}

/**
 * Retrieves the IDs of guests connected on the host ID.
 *
 * @param int $hostId The host ID.
 * @return array The IDs of guests connected on the host ID.
 */
if (!function_exists('getGuestIdsConnectedOnHostId')) {
    function getGuestIdsConnectedOnHostId() {
        return UserConnections::getGuestIdsConnectedOnHostId();
    }
}

/**
 * Retrieves the role name of the current connection user.
 *
 * @return string|null The role name of the current connection user.
 */
if (!function_exists('getCurrentConnectionUserRoleName')) {
    function getCurrentConnectionUserRoleName() {
        $user = auth()->user();

        $currentHostId = getCurrentConnectionByUserId($user->id);

        $getHostConnections = getHostConnections($currentHostId);

        $firstConnection = $getHostConnections->first();

        if($currentHostId == $user->id){
            $userRole = 1;
        }else{
            $userRole = isset($firstConnection->role) ? intval($firstConnection->role) : null;
        }

        $roleName = $userRole ? User::getRoleName($userRole) : null;

        return $roleName ?? null;
    }
}

/**
 * Retrieves the name of the current connection.
 *
 * @return string|null The name of the current connection.
 */
if (!function_exists('getCurrentConnectionName')) {
    function getCurrentConnectionName() {
        $userId = auth()->id();
        $connectionId = UserMeta::getUserMeta($userId, 'current_database_connection');

        $user = getUserData($connectionId);

        return $user->name ?? null;
    }
}

/**
 * Retrieves the name of the connection by its ID.
 *
 * @param int|null $connectionId The ID of the connection.
 * @return string|null The name of the connection.
 */
if (!function_exists('getConnectionNameById')) {
    function getConnectionNameById($connectionId = null) {
        if(!$connectionId){
            $userId = auth()->id();

            $connectionId = UserMeta::getUserMeta($userId, 'current_database_connection');
            $connectionId = intval($connectionId);
        }else{
            $connectionId = intval($connectionId);
        }

        if($connectionId){
            $getUserData = getUserData($connectionId);

            if(auth()->id() === $connectionId){
                $accountName = 'Principal';
            }else{
                $accountName = $getUserData->name;
            }

            return limitChars($accountName, 25);
        }else{
            return null;
        }
    }
}

/**
 * Formats a phone number to the pattern (XX) X XXXX-XXXX.
 *
 * @param string $phoneNumber The phone number to format.
 * @return string The formatted phone number.
 */
if (!function_exists('formatPhoneNumber')) {
    function formatPhoneNumber($phoneNumber) {
        // Remove all non-numeric characters from the phone number.
        $phoneNumber = onlyNumber($phoneNumber);

        // Apply the desired formatting pattern to the phone number.
        return !empty($phoneNumber) ? preg_replace('/(\d{2})(\d{1})(\d{4})(\d{4})/', '($1) $2 $3-$4', $phoneNumber) : '';
    }
}

/**
 * Retrieves subscription data for a specified user.
 *
 * @param int|null $connectionId The ID of the user. Defaults to the authenticated user's ID if not provided.
 * @return array|null The subscription data array if found, or null if not found.
 */
if (!function_exists('getSubscriptionData')) {
    function getSubscriptionData($connectionId = null) {
        if(!$connectionId){
            $connectionId = auth()->id();
        }
        // Fetch user_erp_data where ID is $databaseId
        $query = DB::connection('vpOnboard')->table('users')->where('id', $connectionId)
            ->select([
                'subscription_data',
            ])
            ->first();

        return $query && $query->subscription_data ? json_decode($query->subscription_data, true) : null;
    }
}

/**
 * Retrieves the subscription label for the authenticated user.
 * If the user is an admin and a label exists, it displays a badge with subscription information.
 * If the user does not have a subscription, it prompts to upgrade to a pro account.
 */
if (!function_exists('subscriptionLabel')) {
    function subscriptionLabel(){
        $subscriptionData = getSubscriptionData();

        if($subscriptionData){
            $subscriptionId = $subscriptionData && isset($subscriptionData['subscription_id']) ? $subscriptionData['subscription_id'] : null;
            $subscriptionStatus = $subscriptionData && isset($subscriptionData['subscription_status']) ? $subscriptionData['subscription_status'] : null;

            $status_translated = $subscriptionData ? Stripe::subscriptionStripeStatusTranslation($subscriptionStatus) : null;

            $label = $status_translated['label'] ? $status_translated['label'] : '';
            $description = $status_translated['label'] ? $status_translated['description'] : '';
            $color = $status_translated['label'] ? $status_translated['color'] : '';
            $class = $status_translated['label'] ? $status_translated['class'] : '';

            print Auth::user()->hasRole(User::ROLE_ADMIN) && $label ? '<span class="badge bg-transparent border border-'.$color.' text-'.$color.' float-end text-decoration-none fw-normal small '.$class.'" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-content="'.$description.'" title="'.strtoupper($label).'">'.$label.'</span>' : '';
        }else{
            $userId = auth()->id();
            $currentConnectionId = getCurrentConnectionByUserId($userId);
            if($currentConnectionId == $userId){
                print '<a href="'.route('settingsAccountShowURL').'" class="btn btn-sm btn-outline-theme init-loader" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-content="Maximize seu '.appName().' com nosso plano de assinatura! " title="Conta Gratúita">Atualize para o PRO</a>';

            }
        }
    }
}

/**
 * Extracts the ID of the current database connection from the provided database connection string.
 * The function assumes that the database names follow a pattern like vpApp1, vpApp2, etc., and IDs are numerical.
 *
 * @param string $databaseConnection The name of the database connection.
 * @return int The extracted ID of the database connection.
 */
if (!function_exists('extractDatabaseId')) {
    function extractDatabaseId($databaseConnection) {
        // This depends on how you have structured your database names and IDs
        // If your database names are vpApp1, vpApp2, etc., and IDs are 1, 2, etc.
        $database = onlyNumber($databaseConnection);

        return intval($database);
    }
}

/**
 * Retrieves active companies from the companies table.
 *
 * @return array An array of active company records.
 */
if (!function_exists('getActiveCompanies')) {
    function getActiveCompanies() {
        return DB::connection('vpAppTemplate')
            ->table('companies')
            ->where('status', 1)
            ->orderBy('id')
            ->get()
            ->toArray();
    }
}

/**
 * Retrieves IDs of active companies from the companies table.
 *
 * @return array An array of active company IDs.
 */
if (!function_exists('getActiveCompanieIds')) {
    function getActiveCompanieIds() {
        return DB::connection('vpAppTemplate')
            ->table('companies')
            ->where('status', 1)
            ->orderBy('id')
            ->pluck('id')
            ->toArray();
    }
}

/**
 * Retrieves the company name based on the company ID.
 *
 * @param int $companyId The ID of the company.
 * @return string|null The name of the company, or null if not found.
 */
if (!function_exists('getCompanyNameById')) {
    function getCompanyNameById($companyId){
        if($companyId){
            $companyId = intval($companyId);

            $companyName = DB::connection('vpAppTemplate')
                ->table('companies')
                ->where('id', $companyId)
                ->value('name');

            return $companyName ?: null;
        }
        return null;
    }
}

/**
 * Retrieves active departments from the wlsm_departments table.
 *
 * @return array|null Array of active departments, or null if not found.
 */
if (!function_exists('getActiveDepartments')) {
    function getActiveDepartments() {
        $getActiveDepartments = DB::connection('vpAppTemplate')
        ->table('wlsm_departments')
            ->where('status', 1)
            ->orderBy('department_alias')
            ->get();

        $getActiveDepartments = $getActiveDepartments ?? null;
        return is_string($getActiveDepartments) ? json_decode($getActiveDepartments, true) : $getActiveDepartments;
    }
}

/**
 * Retrieves active warehouse terms based on the provided tracking ID.
 *
 * @param int $trackingId The tracking ID for the warehouse terms.
 * @return array An array containing the active warehouse terms.
 */
if (!function_exists('getWarehouseTerms')) {
    function getWarehouseTerms($trackingId) {
        $data = [];

        $trackingId = intval($trackingId);

        // Set the database connection to vpWarehouse
        $warehouseConnection = DB::connection('vpWarehouse');

        // Fetch terms with their topics
        $terms = $warehouseConnection->table('survey_terms')
            ->where('survey_terms.status', 1)
            ->where('survey_terms.tracking_id', $trackingId)
            ->join('survey_topics', 'survey_terms.id', '=', 'survey_topics.term_id')
            ->orderBy('survey_terms.term_order')
            ->orderBy('survey_topics.topic_order')
            ->select(
                'survey_terms.id AS term_id',
                'survey_terms.name AS term_name',
                'survey_terms.term_order AS term_order',
                'survey_topics.id AS topic_id',
                'survey_topics.question AS topic_question',
                'survey_topics.topic_order AS topic_order',
            )
            ->get();

        // Group topics by term
        foreach ($terms as $term) {
            if (!isset($data[$term->term_id])) {
                $data[$term->term_id] = [
                    'stepData' => [
                        'term_id' => $term->term_id,
                        'term_name' => $term->term_name,
                        'type' => 'default',
                        'original_position' => $term->term_order,
                        'new_position' => $term->term_order,
                    ],
                    'topics' => []
                ];
            }

            $data[$term->term_id]['topics'][] = [
                'topic_id' => $term->topic_id,
                'question' => $term->topic_question,
                'original_position' => $term->topic_order,
                'new_position' => $term->topic_order,
            ];
        }

        // Optional: Re-index array to remove term_id keys
        $data = array_values($data);

        return $data;
    }
}

/**
 * Retrieves active warehouse trackings.
 *
 * @return array An array containing the active warehouse trackings.
 */
if (!function_exists('getWarehouseTrakings')) {
    function getWarehouseTrakings() {
        try {
            return DB::connection('vpWarehouse')
                ->table('survey_trackings')
                ->where('status', 1)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            \Log::error('getWarehouseTrakings: ' . $e->getMessage());

            return [];
        }
    }
}

/**
 * Retrieves the count of warehouse terms associated with a tracking ID.
 *
 * @param int $trackingId The ID of the warehouse tracking.
 * @return int The count of warehouse terms associated with the specified tracking ID.
 */
if (!function_exists('getWarehouseTermsCount')) {
    function getWarehouseTermsCount($trackingId) {
        try {
            // If you have a model, you can use it like Warehouse::on('vpWarehouse')->get();
            return DB::connection('vpWarehouse')
                ->table('survey_terms')
                ->where('tracking_id', $trackingId)
                ->count();
        } catch (\Exception $e) {
            \Log::error('getWarehouseTermsCount: ' . $e->getMessage());

            return 0;
        }
    }
}

/**
 * Retrieves the term name based on the ID.
 *
 * @param int $termId The ID of the term.
 * @return string|null The name of the term, or null if not found.
 */
if (!function_exists('getWarehouseTermNameById')) {
    function getWarehouseTermNameById($termId){
        $termId = intval($termId);

        $termName = DB::connection('vpWarehouse')
            ->table('survey_terms')
            ->where('id', $termId)
            ->value('name');

        return $termName ?: null;
    }
}

/**
 * Retrieves the value of a setting based on the provided key.
 *
 * @param string $key The key of the setting.
 * @return mixed|null The value of the setting, or null if not found.
 */
if (!function_exists('getSettings')) {
    function getSettings($key) {

        if(!$key){
            return null;
        }

        //$dbConnection = DB::connection('vpAppTemplate');
        //\Log::debug('Database getSettings connection: ', ['connection' => $dbConnection->getDatabaseName()]);

        $settingValue = DB::connection('vpAppTemplate')->table('settings')
            ->where('key', $key)
            ->value('value'); // Directly get the value of the 'value' column

        // Return the setting value if found, or null as a default
        return $settingValue ?? null;
    }
}

/**
 * Retrieves the URL of the company logo stored in settings.
 *
 * @return string|null The URL of the company logo, or null if not found.
 */
if (!function_exists('getCompanyLogo')) {
    function getCompanyLogo(){
        $settingsLogo = getSettings('logo');
        // Use the 'getSettings' function which uses a cached version of settings
        return $settingsLogo ? URL::asset('storage/' . $settingsLogo) : null;
    }
}

/**
 * Retrieves the name of the company stored in settings.
 *
 * @return string|null The name of the company, or null if not found.
 */
if (!function_exists('getCompanyName')) {
    function getCompanyName(){
        // Use the 'getSettings' function which uses a cached version of settings
        return getSettings('name');
    }
}

/**
 * Retrieves the Google token stored in settings.
 *
 * @return string|null The Google token, or null if not found.
 */
if (!function_exists('getGoogleToken')) {
    function getGoogleToken(){
        // Use the 'getSettings' function which uses a cached version of settings
        return getSettings('google_token');
    }
}

/**
 * Retrieves the Dropbox token stored in settings.
 *
 * @return string|null The Dropbox token, or null if not found.
 */
if (!function_exists('getDropboxToken')) {
    function getDropboxToken(){
        // Use the 'getSettings' function which uses a cached version of settings
        return getSettings('dropbox_token');
    }
}

/**
 * Generates a status badge based on the provided status.
 *
 * @param string $status The status value ('active', 'trash', 'disabled').
 * @return string The HTML code for the status badge.
 */
if (!function_exists('statusBadge')) {
    function statusBadge($status) {
        switch ($status) {
            case 'active':
                return '<span class="badge bg-success-subtle text-success text-uppercase" title="Registro de Status Ativo">Ativo</span>';
            case 'trash':
                return '<span class="badge bg-danger text-theme text-uppercase" title="Registro de Status Deletado">Deletado</span>';
            case 'disabled':
                return '<span class="badge bg-danger-subtle text-danger text-uppercase" title="Registro de Status Desativado">Desativado</span>';
            default:
                return '';
        }
    }
}

/**
 * Extracts only numeric digits from the given string.
 *
 * @param string|null $number The input string containing numeric and non-numeric characters.
 * @return int The extracted numeric value from the input string.
 */
if (!function_exists('onlyNumber')) {
    function onlyNumber($number = null) {
        if($number){
            $numericValue = preg_replace('/\D/', '', $number);
            return is_numeric($numericValue) ? intval($numericValue) : 0;
        }
        return 0;
    }
}

/**
 * Formats the given number as Brazilian Real currency.
 *
 * @param mixed $number The number to be formatted.
 * @param int $decimalPlaces The number of decimal places to round to (default is 2).
 * @return string The formatted Brazilian Real currency string.
 */
if (!function_exists('brazilianRealFormat')) {
    function brazilianRealFormat($number, $decimalPlaces = 2): string {
        $result = 'R$ 0,00';
        if(!empty($number)){
            $result = 'R$ ' . numberFormat( $number, $decimalPlaces );
        }
        //return !empty($number) && intval($number) > 0 ? 'R$ ' . numberFormat( $number, $decimalPlaces ) : 'R$ 0,00';
        return $result;
    }
}

/**
 * Returns the name of the day of the week in Portuguese for the given date.
 *
 * @param string $date The date string in any valid format supported by Carbon.
 * @return string|null The name of the day of the week in Portuguese or null if $date is empty.
 */
if (!function_exists('dayOfTheWeek')) {
    function dayOfTheWeek($date) {
        if($date){
            // Set the locale to Portuguese
            Carbon::setLocale('pt_BR');

            $date = Carbon::parse($date);

            // Get the day of the week in Portuguese
            return $date->isoFormat('dddd');
        }else{
            return null;
        }
    }
}

/**
 * Formats the given file size into a human-readable format.
 *
 * @param int $size The size of the file in bytes.
 * @return string The formatted size with appropriate unit (Bytes, KB, MB, GB, TB).
 */
if (!function_exists('formatSize')) {
    function formatSize($size) {
        if($size){
            $base = log($size, 1024);
            $suffixes = array('Bytes', 'KB', 'MB', 'GB', 'TB');

            return !empty(onlyNumber($size)) ? round(pow(1024, $base - floor($base)), 2) . ''.$suffixes[floor($base)].'' : 0;
        }
        return 0;
    }
}

/**
 * Formats the given number into a human-readable format with specified decimal places and thousand separator.
 *
 * @param float|int|string $number The number to format.
 * @param int $decimalPlaces The number of decimal places.
 * @return string The formatted number.
 */
if (!function_exists('numberFormat')) {
    function numberFormat($number, $decimalPlaces = 0) {
        if($number){
            $numericValue = is_numeric($number) ? floatval($number) : 0;
            return number_format($numericValue, $decimalPlaces, ',', '.');
        }
        return 0;
    }
}

/**
 * Converts a string representation of a number with decimal and thousand separators into a numeric value.
 *
 * @param string $number The string representation of the number.
 * @return float The numeric value.
 */
if (!function_exists('convertToNumeric')) {
    function convertToNumeric($number) {
        if($number){
            return floatval(str_replace(',', '.', str_replace('.', '', $number)));
        }
        return 0;
    }
}

/**
 * Retrieves the name of a term by its ID from the survey_terms table.
 *
 * @param int|null $termId The ID of the term.
 * @return string|null The name of the term or null if not found.
 */
if (!function_exists('getTermNameById')) {
    function getTermNameById($termId) {
        $term = $termId ? SurveyTerms::find($termId) : null;

        return $term ? $term->name : null;
    }
}

/**
 * Retrieves the name of a survey by its ID from the surveys table.
 *
 * @param int|null $surveyId The ID of the survey.
 * @return string|null The name of the survey or null if not found.
 */
if (!function_exists('getSurveyNameById')) {
    function getSurveyNameById($surveyId) {
        $survey = $surveyId ? Survey::find($surveyId) : null;

        return $survey ? $survey->title : null;
    }
}

/**
 * Retrieves survey data by its ID from the surveys table.
 *
 * @param int|null $surveyId The ID of the survey.
 * @return \App\Models\Survey|null The survey model instance or null if not found.
 */
if (!function_exists('getSurveyDataById')) {
    function getSurveyDataById($surveyId) {
        $survey = $surveyId ? Survey::find($surveyId) : null;

        return $survey ?? null;
    }
}

/**
 * Retrieves the title of a survey template by its ID from the survey_templates table.
 *
 * @param int|null $templateId The ID of the survey template.
 * @return string|null The title of the survey template or null if not found.
 */
if (!function_exists('getSurveyTemplateNameById')) {
    function getSurveyTemplateNameById($templateId) {
        $template = $templateId ? SurveyTemplates::find($templateId) : null;

        return $template ? $template->title : null;
    }
}

/**
 * Retrieves the description of a survey template by its ID from the survey_templates table.
 *
 * @param int|null $templateId The ID of the survey template.
 * @return string|null The description of the survey template or null if not found.
 */
if (!function_exists('getTemplateDescriptionById')) {
    function getTemplateDescriptionById($templateId) {
        $template = $templateId ? SurveyTemplates::find($templateId) : null;

        return $template ? $template->description : null;
    }
}

/**
 * Retrieves the recurring status of a survey template by its ID from the survey_templates table.
 *
 * @param int|null $templateId The ID of the survey template.
 * @return bool|null The recurring status of the survey template or null if not found.
 */
if (!function_exists('getTemplateRecurringById')) {
    function getTemplateRecurringById($templateId) {
        $template = $templateId ? SurveyTemplates::find($templateId) : null;

        return $template ? $template->recurring : null;
    }
}

/**
 * Retrieves the value of a specific cookie by its name.
 *
 * @param string $cookieName The name of the cookie.
 * @return string|null The value of the cookie or null if not found.
 */
if (!function_exists('getCookie')) {
    function getCookie($cookieName) {
        $cookieValue = isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : null;
        //$cookieValue = request()->cookie($cookieName);
        //dd($cookieValue);
        return $cookieValue;
    }
}

/**
 * Limits the characters in a string to a specified length.
 *
 * @param string $text The input string.
 * @param int $number The maximum number of characters.
 * @return string The truncated string.
 */
if (!function_exists('limitChars')) {
    function limitChars($text = '', $number = 50) {
        return !empty(trim($text)) ? \Illuminate\Support\Str::limit($text, $number) : '';
    }
}

/**
 * Determines the progress bar class based on the completion percentage.
 *
 * @param int|float $percentage The completion percentage.
 * @return string The progress bar class.
 */
if (!function_exists('getProgressBarClass')) {
    function getProgressBarClass($percentage){
        if ($percentage >= 100) {
            return 'success'; // Completed
        } elseif ($percentage > 75) {
            return 'info'; // High progress
        } elseif ($percentage > 50) {
            return 'primary'; // Moderate progress
        } elseif ($percentage > 25) {
            return 'warning'; // Low progress
        } else {
            return 'danger'; // Just started or no progress
        }
    }
}

/**
 * Determines the progress bar class based on the storage usage percentage.
 *
 * @param int|float $percentageUsed The storage usage percentage.
 * @return string The progress bar class.
 */
if (!function_exists('getProgressBarClassStorage')) {
    function getProgressBarClassStorage($percentageUsed){
        if ($percentageUsed >= 100) {
            return 'danger'; // Completed
        } elseif ($percentageUsed > 75) {
            return 'warning'; // High progress
        } elseif ($percentageUsed > 50) {
            return 'primary'; // Moderate progress
        } elseif ($percentageUsed > 25) {
            return 'info'; // Low progress
        } else {
            return 'success'; // Just started or no progress
        }
    }
}

/**
 * Prints the given data using print_r if the application is in debug mode.
 *
 * @param mixed $data The data to print.
 * @return void
 */
if(!function_exists('appPrintR')){
	function appPrintR($data){
		/*if( !empty($data) ){
			print '<pre class="language-markup"><code>';
				print_r( $data );
			print '</code></pre>';
		}*/
        if( env('APP_DEBUG') && !empty($data) ){
			print '<pre>';
				print_r( $data );
			print '</pre>';
		}
	}
}

/**
 * Prints the given data using var_export if the application is in debug mode.
 * This function is useful for printing data inside the content body.
 *
 * @param mixed $data The data to print.
 * @return void
 */
if(!function_exists('appPrintR2')){
	function appPrintR2($data){
		if( env('APP_DEBUG') && !empty($data) ){
			print '<pre class="language-markup" style="font-family: inherit; white-space: pre-wrap; color: #87DF01;">'.var_export( $data, true).'</pre>';
		}
	}
}
