<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserMeta;
use App\Models\UserConnections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\OnboardController;
use Illuminate\Support\Facades\Crypt;

/**
 * SettingsUserController
 *
 * Controller responsible for handling user-related actions.
 */
class SettingsUserController extends Controller
{
    //protected $connection = 'vpAppTemplate';

    // Fill created_at
    public $timestamps = true;

    /**
     * Display a listing of all users.
     */
    public function index()
    {
        $getUsersDataConnectedOnMyAccount = getUsersDataConnectedOnMyAccount();

        return view('settings.users', compact('getUsersDataConnectedOnMyAccount'));
    }

    /**
     * Display the specified user's profile.
     */
    public function show($id = null)
    {
        if (!$id && auth()->check()) {
            $user = auth()->user();
        } else {
            $user = User::findOrFail($id);

            // Check if the authenticated user is the same as the user being viewed
            /*if(auth()->id() !== $user->id) {
                abort(403, 'Unauthorized action.');
            }*/
        }

        return view('profile.index', compact('user'));
    }

    /**
     * Store a newly created user in the database.
     */
    public function store(Request $request)
    {
        $subscriptionData = getSubscriptionData();
        $subscriptionStatus = $subscriptionData['subscription_status'] ?? null;
        if($subscriptionStatus != 'active'){
            return response()->json(['success' => false, 'action' => 'subscriptionAlert', 'message' => "Para prosseguir você deverá primeiramente ativar sua assinatura"], 200);
        }

        $messages = [
            'role.required' => 'Selecione o nível.',
            'email.required' => 'Informe o e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'email.max' => 'O e-mail não pode ter mais de 100 caracteres.',
        ];
        $validatedData = $request->validate([
            'email' => 'required|email|max:100',
            'role' => 'required|role',
        ], $messages);

        $hostUser = auth()->user();
        $hostUserName= $currentUser->name;
        $hostUserId = $currentUser->id;
        $hostUserEmail = $currentUser->email;

        $questUserEmail = $validatedData['email'];

        if($hostUserEmail === $questUserEmail){
            return response()->json(['success' => false, 'message' => "O e-mail informado é o principal desta conta e não poderá ser adicionado como colaborador"], 200);
        }

        $guestExists = DB::connection('vpOnboard')
            ->table('users')
            ->where('email', $questUserEmail)
            ->first();

        if ($guestExists){
            $questUserId = $guestExists->id;
            $questUserName = $guestExists->name;
            $questUserEmail = $guestExists->email;

            /*$guestExistsAndIsConnected = DB::connection('vpOnboard')
                ->table('user_connections')
                ->where('user_id', $questUserId)
                ->where('connected_to', $hostUserId)
                ->first();*/
        }

        $getUserDataFromConnectedAccountId = UserConnections::getUserDataFromConnectedAccountId($questUserId, $hostUserId);
        $getQuestUserStatus = isset($getUserDataFromConnectedAccountId->status) ? $getUserDataFromConnectedAccountId->status : null;


        $questUserRole = $request->input('role', 4);
        if (!in_array($questUserRole, [2, 3, 4, 5])) {
            return response()->json(['success' => false, 'message' => "Selecione o Nível"], 200);
        }

        $questUserCompanies = $request->has('companies') && is_array($request->companies) ? json_encode(array_map('intval', $request->companies)) : null;

        $params = array(
            'role' => $questUserRole,
            'companies' => $questUserCompanies
        );
        $paramsEncoded = json_encode($params);

        if ($getQuestUserStatus) {// check if user have a connection with hostUserId
            if( $getQuestUserStatus != 'active'){
                $message = 'O usuário de e-mail '.$questUserEmail.' já possui uma conexão com seu ' . env('APP_NAME') . ' mas seu status foi desativado.<br><br>';

                switch ($questUserConnectionStatus) {
                    case 'inactive':
                        $message .= '&#x2022; Você poderá ativar acessando seu painel em ' . route('settingsAccountShowURL') . '/tab=users';
                        break;
                    case 'waiting':
                        $message .= '&#x2022; Este usuário ainda não aceitou seu convite.';
                        break;
                    case 'revoked':
                        $message .= '&#x2022; Este usuário revogou o acesso. Somente ' . $questUserName . ' poderá reativar.';
                        break;
                }

            }else{
                $message = 'O usuário de e-mail '.$questUserEmail.' já possui uma conexão com seu ' . env('APP_NAME') . '.<br><br>';
                $message .= '&#x2022; Verifique se o Nível está definido como Vistoria ou Auditoria e se tal possui acesso a determinadas Unidades Corporativas';
            }

            return response()->json(['success' => false, 'message' => $message], 200);

        } elseif ($guestExists) {// check if user exists in database
            $connectionCode = Crypt::encryptString($hostUserId . '~~~' . $questUserEmail . '~~~' . $paramsEncoded);

            //Send mail invite notification message for this user
            $content = '
                '.$hostUserName.' está lhe convidando para colaborar em suas tarefas. Para aceitar <a href="' . route('invitationResponseURL') . '/'. $connectionCode . '">clique aqui</a>.<br><br>Se isto foi um erro ou você desconhece <u>' . $hostUserName . '</u>, apenas ignore esta mensagem.
            ';

            appSendEmail($questUserEmail, $questUserName, 'Convite de Colaboração', $content, $template = 'default');

            $message = 'Convite solicitando colaboração foi enviado para <span class="text-theme">' . $questUserName . '</span> no endereço de e-mail <span class="text-theme">' . $questUserEmail . '</span>';
        } else {
            $connectionCode = Crypt::encryptString($hostUserId . '~~~' . $questUserEmail . '~~~' . $paramsEncoded);

            //If user dont exists, create a new one with status '3'. 3 == invited.
            /*$data = [
                'name' => '',
                'email' => $questUserEmail,
                'status' => 2,
            ];
            RegisterController::register($data);*/

            //Send mail message for this user
            $content = '
                '.$hostUserName.' está lhe convidando para colaborar em suas tarefas. Para registrar-se gratuitamente no ' . env('APP_NAME') . ' <a href="' . route('registerURL') . '/' . $connectionCode . '">clique aqui</a>.<br><br>Se isto foi um erro ou você desconhece <u>' . $hostUserName . '</u>, apenas ignore esta mensagem.
            ';

            appSendEmail($questUserEmail, '', 'Convite de Colaboração', $content, $template = 'default');

            $message = 'Convite solicitando colaboração foi enviado para o endereço de e-mail <span class="text-theme">' . $questUserEmail . '</span>';
        }

        return response()->json(['success' => true, 'message' => $message], 200);
    }

    /**
     * Update the specified user in the database.
     */
    public function update(Request $request, $questUserId)
    {
        $subscriptionData = getSubscriptionData();
        $subscriptionStatus = $subscriptionData['subscription_status'] ?? null;
        if($subscriptionStatus != 'active'){
            return response()->json(['success' => false, 'message' => "Para prosseguir você deverá primeiramente ativar sua assinatura"], 200);
        }

        /*$messages = [
            'role.required' => 'Selecione o nível'
        ];
        $validatedData = $request->validate([
            'role' => 'required|role'
        ], $messages);*/

        $hostUser = auth()->user();
        $hostUserId = $hostUser->id;

        $questUser = User::find($questUserId);
        if (!$questUser) {
            return response()->json(['success' => false, 'message' => "Usuário não corresponde a nossa base de dados"], 200);
        }

        $getUserIdsConnectedOnMyAccount = getUserIdsConnectedOnMyAccount();
        if (!in_array($questUser->id, $getUserIdsConnectedOnMyAccount)) {
            return response()->json(['success' => false, 'message' => "Usuário não conectado ao seu " . appName()], 200);
        }

        $getUserDataFromConnectedAccountId = UserConnections::getUserDataFromConnectedAccountId($questUser->id, $hostUserId);
        $getQuestUserStatus = isset($getUserDataFromConnectedAccountId->status) ? $getUserDataFromConnectedAccountId->status : null;

        if($getQuestUserStatus == 'revoked'){
            return response()->json(['success' => false, 'message' => 'Este usuário <span class="text-warning">revogou</span> o acesso. Somente <span class="text-info">' . $questUser->name . '</span> poderá reativar.'], 200);
        }

        // requests
        $questUserRole = $request->input('role');
        $questUserStatus = $request->input('status', 'inactive');
        $questUserCompanies = $request->has('companies') && is_array($request->companies) ? json_encode(array_map('intval', $request->companies)) : null;

        if (!in_array($questUserRole, [2, 3, 4, 5])) {
            return response()->json(['success' => false, 'message' => "Selecione o Nível"], 200);
        }

        UserConnections::setConnectionData($questUser->id, $hostUserId, $questUserRole, $questUserStatus, $questUserCompanies);

        return response()->json(['success' => true, 'message' => "Os dados de usuário foram atualizados"], 200);
    }

    /**
     * Update or create a user's meta data.
     */
    public function updateUserMeta($userId, $metaKey, $metaValue)
    {
        // Update or create user meta
        UserMeta::updateOrCreate(
            ['user_id' => $userId, 'meta_key' => $metaKey],
            ['meta_value' => $metaValue]
        );
    }

    /**
     * Get the content for the user modal.
     */
    public function getUserFormContent(Request $request)
    {
        $user = null;

        $userId = $request->input('userId', null);
        $origin = $request->input('origin', null);

        if ($userId) {
            $user = User::find($userId);
        }

        return view('settings.components.users-form', compact('user', 'origin'));
    }


}
