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
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $getUsersDataConnectedOnMyAccount = getUsersDataConnectedOnMyAccount();

        return view('settings.users', compact('getUsersDataConnectedOnMyAccount'));
    }

    /**
     * Display the specified user's profile.
     *
     * @param int|null $id The user's ID.
     * @return \Illuminate\View\View
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
     *
     * @param \Illuminate\Http\Request $request The incoming request.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|max:100',
        ], [
            'email.required' => 'Informe o e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'email.max' => 'The email may not be greater than 100 characters.',
        ]);

        $currentUser = auth()->user();
        $currentUserId = $currentUser->id;
        $currentUserEmail = $currentUser->email;

        if($currentUserEmail === $validatedData['email']){
            return response()->json(['success' => false, 'message' => "O e-mail informado é o principal desta conta e não poderá ser adicionado como colaborador"], 200);
        }

        $userExists = DB::connection('vpOnboard')
            ->table('users')
            ->where('email', $validatedData['email'])
            ->first();

        $userRole = $request->input('role', 4);
        if (!in_array($userRole, [2, 3, 4, 5])) {
            return response()->json(['success' => false, 'message' => "Selecione o Nível"], 200);
        }

        $companies = $request->has('companies') && is_array($request->companies) ? json_encode(array_map('intval', $request->companies)) : null;

        if ($userExists) {
            UserConnections::setConnectionData($userExists->id, $currentUserId, $userRole, 'waiting', $companies);

            // TODO: Send mail invite notification message for this user
            // TODO: Add topbar notification message for user $userExists->id

            $message = 'Convite solicitando colaboração foi enviado para <span class="text-theme">' . $userExists->name . '</span> no endereço de e-mail <span class="text-theme">' . $userExists->email . '</span>';
        } else {
            // TODO: Send user invite notification message for a new user

            // TODO If new user receive mail and accepted, update data with setConnectionData function

            $message = 'Convite solicitando colaboração foi enviado para o endereço de e-mail <span class="text-theme">' . $validatedData['email'] . '</span>';
        }

        return response()->json(['success' => true, 'message' => $message], 200);
    }


    /**
     * Update the specified user in the database.
     *
     * @param \Illuminate\Http\Request $request The incoming request.
     * @param int $id The user's ID.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $currentUser = auth()->user();
        $currentUserId = $currentUser->id;

        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => "Usuário não corresponde a nossa base de dados"], 200);
        }

        $getUserIdsConnectedOnMyAccount = getUserIdsConnectedOnMyAccount();

        if (!in_array($user->id, $getUserIdsConnectedOnMyAccount ?? [])) {
            return response()->json(['success' => false, 'message' => "Usuário não conectado ao seu " . appName()], 200);
        }

        $userRole = $request->input('role', 4);
        if (!in_array($userRole, [2, 3, 4, 5])) {
            return response()->json(['success' => false, 'message' => "Selecione o Nível"], 200);
        }

        $companies = $request->has('companies') && is_array($request->companies) ? json_encode(array_map('intval', $request->companies)) : null;

        UserConnections::setConnectionData($user->id, $currentUserId, $userRole, $request->filled('status'), $companies);

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
    public function getUserFormContent($id = null)
    {
        $user = null;

        if ($id) {
            $user = User::find($id);
        }

        return view('settings.components.users-form', compact('user'));

    }
}
