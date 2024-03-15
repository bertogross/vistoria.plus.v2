<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\UserMeta;
use App\Models\UserConnections;
use App\Models\Survey;
use App\Models\SurveyTerms;
use App\Models\SurveyTopic;
use App\Models\SurveyResponse;
use App\Models\SurveyTemplates;
use App\Models\SurveyAssignments;
use App\Models\Stripe;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\PostmarkappController;
use Illuminate\Support\Facades\Storage;

// Set the locale to Brazilian Portuguese
Carbon::setLocale('pt_BR');

if (!function_exists('appName')) {
    function appName(){
        return env('APP_NAME');
    }
}

if (!function_exists('appDescription')) {
    function appDescription(){
        return 'Garantindo Excelência Operacional';
    }
}

if (!function_exists('appSendEmail')) {
    // usage example  : appSendEmail('bertogross@gmail.com', 'customer name here', 'subject here', 'content here with <strong>strong</strong>', 'welcome');
    function appSendEmail($to, $name, $subject, $content, $template = 'default'){
        try{
            return PostmarkappController::sendEmail($to, $name, $subject, $content, $template);
        } catch (\Exception $e) {
            \Log::error('appSendEmail: ' . $e->getMessage());

            return false;
        }
    }
}

if (!function_exists('setDatabaseConnection')) {
    function setDatabaseConnection(){
        $userId = auth()->id();

        $currentConnectionId = getCurrentConnectionByUserId($userId);

        if ($currentConnectionId) {
            $databaseName = 'vpApp' . $currentConnectionId;

            if (!DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$databaseName])) {
                return;
            }

            config(['database.connections.vpAppTemplate.database' => $databaseName]);
        }
    }
}

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

// Get users from vpOnboard table
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

if( !function_exists('getDiskQuota') ){
    function getDiskQuota($connectionId = null) {
        if(!$connectionId){
            $connectionId = auth()->id();
        }

        $getUserData = getUserData($connectionId);

        $diskQuota = $getUserData->disk_quota;
    }
}

if( !function_exists('checkFreeDiskSpace') ){
    function checkFreeDiskSpace($connectionId = null) {
        if(!$connectionId){
            $connectionId = auth()->id();
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

// Retrieve a user's meta value based on the given key.
if (!function_exists('getUserMeta')) {
    function getUserMeta($userId, $key) {
        return UserMeta::getUserMeta($userId, $key);
    }
}

if (!function_exists('getCurrentConnectionId')) {
    function getCurrentConnectionByUserId($userId = false) {
        if(!$userId){
            $userId = auth()->id();
        }
        $connectionId = UserMeta::getUserMeta($userId, 'current_database_connection');

        return $connectionId ? intval($connectionId) : null;
    }
}

if (!function_exists('getCurrentConnectionId')) {
    function getCurrentConnectionId() {
        $userId = auth()->id();

        $connectionId = UserMeta::getUserMeta($userId, 'current_database_connection');

        return $connectionId ? intval($connectionId) : null;
    }
}


if (!function_exists('getGuestConnections')) {
    function getGuestConnections() {
        return UserConnections::getGuestConnections();
    }
}

if (!function_exists('getHostConnections')) {
    function getHostConnections() {
        return UserConnections::getHostConnections();
    }
}

if (!function_exists('getGuestIdsConnectedOnHostId')) {
    function getGuestIdsConnectedOnHostId() {
        return UserConnections::getGuestIdsConnectedOnHostId();
    }
}

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

if (!function_exists('getCurrentConnectionName')) {
    function getCurrentConnectionName() {
        $userId = auth()->id();
        $connectionId = UserMeta::getUserMeta($userId, 'current_database_connection');

        $user = getUserData($connectionId);

        return $user->name ?? null;
    }
}

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

// Format a phone number to the pattern (XX) X XXXX-XXXX.
if (!function_exists('formatPhoneNumber')) {
    function formatPhoneNumber($phoneNumber) {
        // Remove all non-numeric characters from the phone number.
        $phoneNumber = onlyNumber($phoneNumber);

        // Apply the desired formatting pattern to the phone number.
        return !empty($phoneNumber) ? preg_replace('/(\d{2})(\d{1})(\d{4})(\d{4})/', '($1) $2 $3-$4', $phoneNumber) : '';
    }
}

// Get Subscription data
if (!function_exists('getSubscriptionData')) {
    function getSubscriptionData($connectionId = null) {
        if(!$connectionId){
            $connectionId = auth()->id();
        }
        // Fetch user_erp_data where ID is $databaseId
        $query = User::where('id', $connectionId)
            ->select([
                'subscription_data',
            ])
            ->first();

        return $query && $query->subscription_data ? json_decode($query->subscription_data, true) : null;
    }
}

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
            //print '<span class="badge bg-transparent border border-warning text-warning float-end text-decoration-none fw-normal small " data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-content="" title="Conta Gratúita">Free</span>';
        }
    }
}

// Logic to get the ID of the current database connection
if (!function_exists('extractDatabaseId')) {
    function extractDatabaseId($databaseConnection) {
        // This depends on how you have structured your database names and IDs
        // If your database names are vpApp1, vpApp2, etc., and IDs are 1, 2, etc.
        $database = onlyNumber($databaseConnection);

        return intval($database);
    }
}

// Get active companies from the companies table.
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

// Get active companies IDs from the companies table.
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

// Get the company alias based on the company ID
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

// Get active departments from the wlsm_departments table.
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

// Get active wharehouse terms.
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

if (!function_exists('getWarehouseTrakings')) {
    function getWarehouseTrakings() {
        try {
            // If you have a model, you can use it like Warehouse::on('vpWarehouse')->get();
            return DB::connection('vpWarehouse')
                ->table('survey_trackings')
                ->where('status', 1)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            // Handle exceptions, log errors, or return a default value as needed
            return [];
        }
    }
}

if (!function_exists('getWarehouseTermsCount')) {
    function getWarehouseTermsCount($trackingId) {
        try {
            // If you have a model, you can use it like Warehouse::on('vpWarehouse')->get();
            return DB::connection('vpWarehouse')
                ->table('survey_terms')
                ->where('tracking_id', $trackingId)
                ->count();
        } catch (\Exception $e) {
            // Handle exceptions, log errors, or return a default value as needed
            return 0;
        }
    }
}

// Get the term name based on the ID
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

if (!function_exists('getSettings')) {
    function getSettings($key) {
        // Static variable to hold the settings array
        static $settingsCache = null;

        // Check if settings have already been loaded
        if ($settingsCache === null) {
            // Load settings and cache them
            $settingsCache = DB::connection('vpAppTemplate')->table('settings')->pluck('value', 'key')->toArray();
        }

        // Return the setting if it exists, or an empty string as a default
        return isset($settingsCache[$key]) ? $settingsCache[$key] : '';
    }
}

if (!function_exists('getCompanyLogo')) {
    function getCompanyLogo(){
        $getSettingsLogo = getSettings('logo');
        // Use the 'getSettings' function which uses a cached version of settings
        return $getSettingsLogo ? URL::asset('storage/' . $getSettingsLogo) : null;
    }
}

if (!function_exists('getCompanyName')) {
    function getCompanyName(){
        // Use the 'getSettings' function which uses a cached version of settings
        return getSettings('name');
    }
}

if (!function_exists('getGoogleToken')) {
    function getGoogleToken(){
        // Use the 'getSettings' function which uses a cached version of settings
        return getSettings('google_token');
    }
}

if (!function_exists('getDropboxToken')) {
    function getDropboxToken(){
        // Use the 'getSettings' function which uses a cached version of settings
        return getSettings('dropbox_token');
    }
}

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

if (!function_exists('onlyNumber')) {
    function onlyNumber($number = null) {
        if($number){
            $numericValue = preg_replace('/\D/', '', $number);
            return is_numeric($numericValue) ? intval($numericValue) : 0;
        }
        return 0;
    }
}

// Format a number as Brazilian Real
if (!function_exists('brazilianRealFormat')) {
    function brazilianRealFormat($number, $decimalPlaces = 2): string {
        return !empty($number) && intval($number) > 0 ? 'R$ ' . numberFormat( $number, $decimalPlaces ) : 'R$ 0,00';
    }
}

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

if (!function_exists('numberFormat')) {
    function numberFormat($number, $decimalPlaces = 0) {
        if($number){
            $numericValue = is_numeric($number) ? floatval($number) : 0;
            return number_format($numericValue, $decimalPlaces, ',', '.');
        }
        return 0;
    }
}

if (!function_exists('convertToNumeric')) {
    function convertToNumeric($number) {
        if($number){
            return floatval(str_replace(',', '.', str_replace('.', '', $number)));
        }
        return 0;
    }
}

if (!function_exists('getTermNameById')) {
    function getTermNameById($termId) {
        $term = $termId ? SurveyTerms::find($termId) : null;

        return $term ? $term->name : null;
    }
}

if (!function_exists('getSurveyNameById')) {
    function getSurveyNameById($surveyId) {
        $survey = $surveyId ? Survey::find($surveyId) : null;

        return $survey ? $survey->title : null;
    }
}

if (!function_exists('getSurveyTemplateNameById')) {
    function getSurveyTemplateNameById($templateId) {
        $template = $templateId ? SurveyTemplates::find($templateId) : null;

        return $template ? $template->title : null;
    }
}

if (!function_exists('getTemplateDescriptionById')) {
    function getTemplateDescriptionById($templateId) {
        $template = $templateId ? SurveyTemplates::find($templateId) : null;

        return $template ? $template->description : null;
    }
}

if (!function_exists('getTemplateRecurringById')) {
    function getTemplateRecurringById($templateId) {
        $template = $templateId ? SurveyTemplates::find($templateId) : null;

        return $template ? $template->recurring : null;
    }
}

// Get the value of a specific cookie by its name
if (!function_exists('getCookie')) {
    function getCookie($cookieName) {
        $cookieValue = isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : null;
        //$cookieValue = request()->cookie($cookieName);
        //dd($cookieValue);
        return $cookieValue;
    }
}

//Max length (limit chars)
if (!function_exists('limitChars')) {
    function limitChars($text = '', $number = 50) {
        return !empty(trim($text)) ? \Illuminate\Support\Str::limit($text, $number) : '';
    }
}

// Get the progress bar class based on the completion percentage
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

//Useful to see on the bottom left side fixed smotth fixed div
if(!function_exists('appPrintR')){
	function appPrintR($data){
		/*if( !empty($data) ){
			print '<pre class="language-markup"><code>';
				print_r( $data );
			print '</code></pre>';
		}*/
        if( !empty($data) ){
			print '<pre>';
				print_r( $data );
			print '</pre>';
		}
	}
}

//Useful to print inside the content body
if(!function_exists('appPrintR2')){
	function appPrintR2($data){
		if( !empty($data) ){
			print '<pre class="language-markup" style="font-family: inherit; white-space: pre-wrap; color: #87DF01;">'.var_export( $data, true).'</pre>';
		}
	}
}
