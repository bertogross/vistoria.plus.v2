<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserMeta;
use App\Models\UserConnections;
use Illuminate\Support\Str;
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
        $userExistsAndIsConnected = null;

        $validatedData = $request->validate([
            'email' => 'required|email|max:100',
        ], [
            'email.required' => 'Informe o e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'email.max' => 'O e-mail não pode ter mais de 100 caracteres.',
        ]);

        $currentUser = auth()->user();
        $currentUserName= $currentUser->name;
        $currentUserId = $currentUser->id;
        $currentUserEmail = $currentUser->email;

        if($currentUserEmail === $validatedData['email']){
            return response()->json(['success' => false, 'message' => "O e-mail informado é o principal desta conta e não poderá ser adicionado como colaborador"], 200);
        }

        $userExists = DB::connection('vpOnboard')
            ->table('users')
            ->where('email', $validatedData['email'])
            ->first();

        if($userExists){
            $userExistsAndIsConnected = DB::connection('vpOnboard')
                ->table('user_connections')
                ->where('user_id', $userExists->id)
                ->where('connected_to', $currentUserId)
                ->first();
        }

        $userRole = $request->input('role', 4);
        if (!in_array($userRole, [2, 3, 4, 5])) {
            return response()->json(['success' => false, 'message' => "Selecione o Nível"], 200);
        }

        $companies = $request->has('companies') && is_array($request->companies) ? json_encode(array_map('intval', $request->companies)) : null;

        $params = array(
            'role' => $userRole,
            'companies' => $companies
        );
        $paramsEncoded = json_encode($params);


        if ($userExistsAndIsConnected) {// check if user have a connection with currentUserId
            $connectionStatus = $userExistsAndIsConnected->connection_status;
            if( $connectionStatus != 'active'){
                $message = 'O usuário de e-mail '.$validatedData['email'].' já possui uma conexão com seu ' . env('APP_NAME') . ' mas seu status foi desativado.<br><br>';

                switch ($connectionStatus) {
                    case 'inactive':
                        $message .= '&#x2022; Você poderá ativar acessando seu painel em ' . route('settingsAccountShowURL') . '/tab=users';
                        break;
                    case 'waiting':
                        $message .= '&#x2022; Este usuário ainda não aceitou seu convite';
                        break;
                    case 'revoked':
                        $message .= '&#x2022; Este usuário revogou o acesso e somente esta pessoa poderá reativar.';
                        break;
                }

            }else{
                $message = 'O usuário de e-mail '.$validatedData['email'].' já possui uma conexão com seu ' . env('APP_NAME') . '.<br><br>';
                $message .= '&#x2022; Verifique se o Nível está definido como Vistoria ou Auditoria e se tal possui acesso a determinadas Unidades Corporativas';
            }

            return response()->json(['success' => false, 'message' => $message], 200);

        } elseif ($userExists) {// check if user exists in database
            $connectionCode = Crypt::encryptString($currentUserId . '~~~' . $userExists->id . '~~~' . $paramsEncoded);

            UserConnections::setConnectionData($userExists->id, $currentUserId, $userRole, 'waiting', $companies, $connectionCode);

            //Send mail invite notification message for this user
            $content = '
                '.$currentUserName.' está lhe convidando para colaborar em suas tarefas. Para aceitar <a href="' . route('invitationResponseURL') . '/'. $connectionCode . '">clique aqui</a>.<br><br>Se isto foi um erro ou você desconhece <u>' . $currentUserName . '</u>, apenas ignore esta mensagem.
            ';

            appSendEmail($userExists->email, $userExists->name, 'Convite de Colaboração', $content, $template = 'default');

            // TODO: Add topbar notification message for user $userExists->id

            $message = 'Convite solicitando colaboração foi enviado para <span class="text-theme">' . $userExists->name . '</span> no endereço de e-mail <span class="text-theme">' . $userExists->email . '</span>';
        } else {
            //Send mail invite notification message for this user
            $content = '
                '.$currentUserName.' está lhe convidando para colaborar em suas tarefas. Para registrar-se gratuitamente no ' . env('APP_NAME') . ' <a href="' . route('registerURL') . '">clique aqui</a>.<br><br>Se isto foi um erro ou você desconhece <u>' . $currentUserName . '</u>, apenas ignore esta mensagem.
            ';

            appSendEmail($validatedData['email'], '', 'Convite de Colaboração', $content, $template = 'default');

            $message = 'Convite solicitando colaboração foi enviado para o endereço de e-mail <span class="text-theme">' . $validatedData['email'] . '</span>';
        }

        return response()->json(['success' => true, 'message' => $message], 200);
    }


    /**
     * Update the specified user in the database.
     */
    public function update(Request $request, $id)
    {
        $currentUser = auth()->user();
        $currentUserId = $currentUser->id;

        $getUserIdsConnectedOnMyAccount = getUserIdsConnectedOnMyAccount();

        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => "Usuário não corresponde a nossa base de dados"], 200);
        }

        // requests
        $userRole = $request->input('role', 4);
        $status = $request->input('status', 'inactive');
        $companies = $request->has('companies') && is_array($request->companies) ? json_encode(array_map('intval', $request->companies)) : null;

        if (!in_array($user->id, $getUserIdsConnectedOnMyAccount)) {
            return response()->json(['success' => false, 'message' => "Usuário não conectado ao seu " . appName()], 200);
        }

        if (!in_array($userRole, [2, 3, 4, 5])) {
            return response()->json(['success' => false, 'message' => "Selecione o Nível"], 200);
        }

        UserConnections::setConnectionData($user->id, $currentUserId, $userRole, $status, $companies);

        return response()->json(['success' => true, 'message' => "Permissões de usuário foram atualizadas"], 200);
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
     *
     * @param int|null $id The user's ID.
     * @return \Illuminate\View\View
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
