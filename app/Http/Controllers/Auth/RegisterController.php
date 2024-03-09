<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\UserConnections;
use Illuminate\Support\Facades\Crypt;

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function welcome()
    {
        return view('auth.register-success');
    }

    /**
     * Create a new user instance after a valid registration.
     */
    protected function register(Request $request)
    {
        $data = $request->all();

        // Define custom messages
        $messages = [
            'name.required' => 'Informe seu nome.',
            'name.string' => 'O nome deve conter letras.',
            'name.max' => 'O nome deve contar no máximo 100 caracteres.',
            'email.required' => 'Informe um e-mail.',
            'email.string' => 'Informe um e-mail válido.',
            'email.email' => 'Informe um e-mail válido.',
            'email.max' => 'O e-mail não deve conter mais de 150 caracteres.',
            'email.unique' => 'O e-mail '.$data['email'].' já está sendo utilizado nesta plataforma.',
        ];
        $validator = Validator::make($data, [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:150|unique:users',
        ], $messages);

        /*$userExists = User::findUserByEmail($data['email']);
        if($userExists){
            return redirect()->back()->withErrors(['email' => 'E-mail '.$data['email'].' já existe na base de dados']);
        }*/

        if ($validator->fails()) {
            // Redirect back with input and errors
            // The withInput() method flashes the old input data to the session
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }

        // Generate a 16-character random string
        $password = Str::random(16);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($password),
            'status' => $data['status'] ?? 1, // status:  0 = inactive | 1 = active | 2 = invited
        ]);

        if($user){
            // Create user database based on vpDefaultSchema
            UserConnections::duplicateAndRenameDefaultSchema($user->id);

            // START Connect to another account
            $hostUserId = $request->host_user_id ?? null;
            $questUserParams = $request->quest_user_params ?? null;
            if($hostUserId && $questUserParams){
                $questUserId = $user->id;
                $decodeQuestUserParams = $questUserParams ? json_decode($questUserParams, true) : null;
                    $guestUserRole = $decodeQuestUserParams->role ?? 4;
                    $questUserCompanies = $decodeQuestUserParams->companies ?? [];

                UserConnections::setConnectionData($questUserId, $hostUserId, $guestUserRole, 'active', $questUserCompanies);
            }
            // END Connect to another account


            $content = '<br><strong style="font-weight:600;">E-mail de Login: </strong>' . $data['email'];
            $content .= '<br><strong style="font-weight:600;">Senha: </strong>' . $password;

            // Send e-mail with welcome tempalte message and login data
            if(appSendEmail($data['email'], $data['name'], 'Seu Registro no ' . appName(), $content, $template = 'welcome')){
                return redirect()->route('registerSuccessURL')->with('success', 'Registro realizado com sucesso!<br>Uma mensagem contendo os dados de acesso foram enviados ao e-mail ' . $data['email'] . '<br><br>Você também poderá copiar as seguintes informações:' . $content);
            }else{
                return redirect()->route('registerSuccessURL')->with('success', 'Registro realizado com sucesso!<br><br>Copie seus dados de acesso:' . $content);
            }
        }else{
            return redirect()->back()->withError(['Não foi possível prosseguir com o registro. Tente novamente mais tarde.']);
        }
    }


    public function invitationResponse($connectionCode = null)
    {
        if($connectionCode){
            $decryptedValue = Crypt::decryptString($connectionCode);
            $connectionCode = explode('~~~', $decryptedValue);

        }else{
            $connectionCode = null;
        }

        return view('auth.invitation', compact('connectionCode'));

    }

}
