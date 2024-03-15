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
//use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

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
            'register_name.required' => 'Informe seu nome.',
            'register_name.string' => 'O nome deve conter letras.',
            'register_name.max' => 'O nome deve contar no máximo 150 caracteres.',
            'register_email.required' => 'Informe um e-mail.',
            'register_email.string' => 'Informe um e-mail válido.',
            'register_email.email' => 'Informe um e-mail válido.',
            'register_email.max' => 'O e-mail não deve conter mais de 150 caracteres.',
            'register_email.unique' => 'O e-mail <u>'.$data['register_email'].'</u> já está sendo utilizado nesta plataforma.<br><strong class="text-warning">Preencha o formulário de Login.</strong>',
        ];
        $validator = Validator::make($data, [
            'register_name' => 'required|string|max:150',
            'register_email' => 'required|string|email|max:150|unique:users,email',
        ], $messages);

        if ($validator->fails()) {
            // Redirect back with input and errors
            // The withInput() method flashes the old input data to the session
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }

        $userExists = User::findUserByEmail($data['register_email']);
        if($userExists){
            return redirect()->back()->withErrors(['register_email' => 'O e-mail <u>'.$data['register_email'].'</u> já está sendo utilizado nesta plataforma.<br><strong class="text-warning">Preencha o formulário de Login.</strong>']);
        }

        // Generate a 16-character random string
        $password = Str::random(16);

        $user = User::create([
            'name' => $data['register_name'],
            'email' => $data['register_email'],
            'password' => Hash::make($password),
            'status' => $data['status'] ?? 1, // status:  0 = inactive | 1 = active | 2 = invited
        ]);

        if($user){
            // Create user database based on vpDefaultSchema
            UserConnections::duplicateAndRenameDefaultSchema($user->id);

            // Connect to another account
            UserConnections::acceptConnection($request, $user->id);

            $content = '<br><strong style="font-weight:600;">E-mail de Login: </strong>' . $data['register_email'];
            $content .= '<br><strong style="font-weight:600;">Senha: </strong>' . $password;

            // Send e-mail with welcome tempalte message and login data
            if(appSendEmail($data['register_email'], $data['register_name'], 'Seu Registro no ' . appName(), $content, $template = 'welcome')){
                return redirect()->route('registerSuccessURL')->with('success', 'Registro realizado com sucesso!<br>Uma mensagem contendo os dados de acesso foram enviados ao e-mail ' . $data['register_email'] . '<br><br>Você também poderá copiar as seguintes informações:' . $content);
            }else{
                return redirect()->route('registerSuccessURL')->with('success', 'Registro realizado com sucesso!<br><br>Copie seus dados de acesso:' . $content);
            }
        }else{
            return redirect()->back()->withError(['Não foi possível prosseguir com o registro. Tente novamente mais tarde.']);
        }
    }


    public function invitationResponse(Request $request, $connectionCode = null)
    {
        $hostUserId = $guestUserParams = $hostUserIdCookie = $guestUserParamsCookie = $guestExists = $guestUserEmail = null;

        if ($connectionCode) {
            $decryptedValue = Crypt::decryptString($connectionCode);
            $connectionCodeParts = $decryptedValue ? explode('~~~', $decryptedValue) : null;

            $hostUserId = $connectionCodeParts[0] ? Crypt::encryptString($connectionCodeParts[0]) : null;
            //$guestUserParams = $connectionCodeParts[2] ? Crypt::encryptString($connectionCodeParts[2]) : null;

            // Create cookies
            //$hostUserIdCookie = cookie('vistoriaplus_hostUserId', $hostUserId, 60*24*30); // Expires in 30 days
            //$guestUserParamsCookie = cookie('vistoriaplus_questUserParams', $guestUserParams, 60*24*30); // Expires in 30 days

            $guestUserEmailFromInvitation = $connectionCodeParts[1] ?? null;

            if($guestUserEmailFromInvitation){
                $guestExists = DB::connection('vpOnboard')
                    ->table('users')
                    ->where('email', $guestUserEmailFromInvitation)
                    ->first();
                $guestUserEmail = $guestExists->email ?? null;
            }

            $hostUser = User::find($connectionCodeParts[0]);
            $hostUserName = $hostUser->name ?? '';

        }

        // Attach cookies to the response
        return response()->view('auth.invitation', compact(
                    'connectionCode',
                    'connectionCodeParts',
                    'hostUserId',
                    'hostUserName',
                    //'questUserParams',
                    //'questUserEmailFromInvitation',
                    'guestExists',
                    'questUserEmail'
                )
            );
            //->cookie($hostUserIdCookie)
            //->cookie($guestUserParamsCookie);
    }


}
