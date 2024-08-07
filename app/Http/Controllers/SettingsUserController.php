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
        $getGuestConnections = getGuestConnections();

        return view('settings.users', compact('getGuestConnections'));
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
        $hostUser = auth()->user();
        $hostUserName= $hostUser->name;
        $hostId = $hostUser->id;
        $hostUserEmail = $hostUser->email;

        $subscriptionData = getSubscriptionData($hostId);
        $subscriptionStatus = $subscriptionData['subscription_status'] ?? null;
        if($subscriptionStatus != 'active'){
            return response()->json(['success' => false, 'action' => 'subscriptionAlert', 'message' => "Para prosseguir você deverá primeiramente ativar sua assinatura"], 200);
        }

        $getQuestUserStatus = null;

        $messages = [
            //'role.required' => 'Selecione o nível.',
            //'role.in' => 'O nível selecionado é inválido',
            'email.required' => 'Informe o e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'email.max' => 'O e-mail não pode ter mais de 100 caracteres.',
        ];
        $validatedData = $request->validate([
            'email' => 'required|email|max:100',
            //'role' => 'required|in:2,3,4'
        ], $messages);

        $guestUserEmail = $validatedData['email'];

        if($hostUserEmail === $guestUserEmail){
            return response()->json(['success' => false, 'message' => "O e-mail informado é o principal desta conta e não poderá ser adicionado como colaborador"], 200);
        }

        // check if user exists in database
        $guestExists = DB::connection('vpOnboard')
            ->table('users')
            ->where('email', $guestUserEmail)
            ->first();

        if ($guestExists){
            $guestId = $guestExists->id;
            $guestUserName = $guestExists->name;
            $guestUserEmail = $guestExists->email;

            $getGuestDataFromConnectedHostId = UserConnections::getGuestDataFromConnectedHostId($guestId, $hostId);
            $getQuestUserStatus = isset($getGuestDataFromConnectedHostId->status) ? $getGuestDataFromConnectedHostId->status : null;
        }

        /*
        $guestUserRole = $request->input('role', 3);
        if (!in_array($guestUserRole, [2, 3, 4, 5])) {
            return response()->json(['success' => false, 'message' => "Selecione o Nível"], 200);
        }

        $guestUserCompanies = $request->has('companies') && is_array($request->companies) ? $request->companies : null;

        $params = array(
            'role' => $guestUserRole,
            'companies' => $guestUserCompanies
        );
        $paramsEncoded = json_encode($params);
        */

        if ($guestExists && $getQuestUserStatus) {// check if quest user have a connection with hostId
            if( $getQuestUserStatus != 'active'){
                $message = 'O usuário de e-mail <strong>'.$guestUserEmail.'</strong> ('.$guestUserName.') já possui uma conexão com seu ' . env('APP_NAME') . ' mas seu status não está ativo.<br><br>';

                switch ($getQuestUserStatus) {
                    case 'inactive':
                        $message .= '<span class="text-warning fs-14">Você poderá ativar acessando seu painel em <u><a href="'.route('settingsAccountShowURL').'/tab=users">' . route('settingsAccountShowURL') . '/tab=users</a></u></span>';
                        break;
                    case 'waiting':
                        $message .= '<span class="text-warning fs-14">Este usuário ainda não aceitou seu convite.</span>';
                        break;
                    case 'revoked':
                        $message .= '<span class="text-warning fs-14">Este usuário revogou o acesso. Somente ' . $guestUserName . ' poderá reativar.</span>';
                        break;
                }
            }else{
                $message = 'O usuário de e-mail '.$guestUserEmail.' (<strong>'.$guestUserName.'</strong>) já possui uma conexão com seu ' . env('APP_NAME') . '.<br><br>';
            }

            return response()->json(['success' => false, 'message' => $message], 200);

        } elseif ($guestExists) {
            // set connection and if origin == survey  surveyReloadUsersTab
            UserConnections::setConnectionData($guestId, $hostId, 3, 'waiting', null);

            //$connectionCode = Crypt::encryptString($hostId . '~~~' . $guestUserEmail . '~~~' . $paramsEncoded);
            $connectionCode = Crypt::encryptString($hostId . '~~~' . $guestUserEmail);

            //Send mail invite notification message for this user
            $content = '
                <strong>'.$hostUserName.'</strong> está lhe convidando para colaborar em suas tarefas. Para aceitar <a href="' . route('invitationResponseURL') . '/'. $connectionCode . '">clique aqui</a>.<br><br><small>Se isto foi um erro e/ou você desconhece <u>' . $hostUserName . '</u>, apenas ignore esta mensagem</small>.
            ';

            appSendEmail($guestUserEmail, $guestUserName, 'Convite de Colaboração', $content, $template = 'default');

            $message = 'Convite solicitando colaboração foi enviado para <span class="text-theme">' . $guestUserName . '</span> no endereço de e-mail <span class="text-theme">' . $guestUserEmail . '</span>';


            return response()->json(['success' => true, 'message' => $message], 200);

        } else {
            //$connectionCode = Crypt::encryptString($hostId . '~~~' . $guestUserEmail . '~~~' . $paramsEncoded);
            $connectionCode = Crypt::encryptString($hostId . '~~~' . $guestUserEmail);

            //Send mail message for this user
            $content = '
                <strong>'.$hostUserName.'</strong> está lhe convidando para colaborar em suas tarefas. Para registrar-se gratuitamente no ' . env('APP_NAME') . ' <a href="' . route('invitationResponseURL') . '/' . $connectionCode . '">clique aqui</a>.<br><br><small>Se isto foi um erro e/ou você desconhece <u>' . $hostUserName . '</u>, apenas ignore esta mensagem</small>.
            ';

            appSendEmail($guestUserEmail, '', 'Convite de Colaboração', $content, $template = 'default');

            $message = 'Convite solicitando colaboração foi enviado para o endereço de e-mail <span class="text-theme">' . $guestUserEmail . '</span>';

            return response()->json(['success' => true, 'message' => $message], 200);
        }
    }

    /**
     * Update the specified user in the database.
     */
    public function update(Request $request, $guestId)
    {
        $hostUser = auth()->user();
        $hostId = $hostUser->id;

        $subscriptionData = getSubscriptionData($hostId);
        $subscriptionStatus = $subscriptionData['subscription_status'] ?? null;
        if($subscriptionStatus != 'active'){
            return response()->json(['success' => false, 'message' => "Para prosseguir você deverá primeiramente ativar sua assinatura"], 200);
        }

        /*
        $messages = [
            'role.required' => 'Selecione o nível',
            'role.in' => 'O nível selecionado é inválido',
        ];

        $validatedData = $request->validate([
            'role' => 'required|in:2,3,4'
        ], $messages);
        */

        $guestUser = User::find($guestId);
        if (!$guestUser) {
            return response()->json(['success' => false, 'message' => "Usuário não corresponde a nossa base de dados"], 200);
        }

        $getGuestIdsConnectedOnHost = getGuestIdsConnectedOnHostId();
        if (!in_array($guestUser->id, $getGuestIdsConnectedOnHost)) {
            return response()->json(['success' => false, 'message' => "Usuário não conectado ao seu " . appName()], 200);
        }

        $getGuestDataFromConnectedHostId = UserConnections::getGuestDataFromConnectedHostId($guestUser->id, $hostId);
        $getQuestUserStatus = isset($getGuestDataFromConnectedHostId->status) ? $getGuestDataFromConnectedHostId->status : null;

        if($getQuestUserStatus == 'revoked'){
            return response()->json(['success' => false, 'message' => 'Este usuário <span class="text-warning">revogou</span> o acesso. Somente <span class="text-info">' . $guestUser->name . '</span> poderá reativar.'], 200);
        }

        // requests
        //$guestUserRole = $request->input('role');
        $guestUserRole = 3;
        $guestUserStatus = $request->input('status', 'inactive');
        //$guestUserCompanies = $request->has('companies') && is_array($request->companies) ? json_encode(array_map('intval', $request->companies)) : null;
        $guestUserCompanies = null;

        if (!in_array($guestUserRole, [2, 3, 4, 5])) {
            return response()->json(['success' => false, 'message' => "Selecione o Nível"], 200);
        }

        $UserConnections = UserConnections::setConnectionData($guestUser->id, $hostId, $guestUserRole, $guestUserStatus, $guestUserCompanies);
        if($UserConnections){
            return response()->json(['success' => true, 'message' => "O status de usuário foi atualizado"], 200);
        }else{
            return response()->json(['success' => false, 'message' => "Erro ao atualizar status de usuário"], 400);
        }

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
