<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class ResetPasswordController extends Controller
{

    // Send the password reset link email
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $request->email)->firstOrFail();

        if(!$user){
            return redirect()->back()->withErrors(['email' => 'E-mail inexistente em nossa na base de dados'])->withInput();;
        }

        $email = $user->email;
        $name = $user->name;
        
        // Specify the database connection if it's not the default one
        $connection = DB::connection('vpOnboard');

        // Check if a token already exists for the given email and delete it
        $connection->table('password_resets')->where('email', $email)->delete();

        // Generate a token
        $token = Str::random(60);

        // Now, insert the new token
        $connection->table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        // Prepare the URL with the token
        $resetUrl = route('passwordResetFormURL', ['token' => $token, 'email' => $email]);
        $content = 'Você solicitou a redefinição de sua senha?<br><br> Para prosseguir <a href=" ' . $resetUrl. ' ">clique aqui</a>.<br><p>Se não foi você ou isto foi um engano, por favor ignore esta mensagem e nenhuma alteração será realizada.</p>';
        
        if(appSendEmail($email, $name, 'Redefinição de Senha', $content, 'reset-password')){
            return view('auth.passwords.confirm', compact('email'));
        }else{
            return redirect()->back()->withErrors(['email' => 'Não foi possível executar sua solicitação. Tente novamente mais tarde.'])->withInput();
        }

    }

    // Show the password reset form
    public function showResetForm(Request $request, $token = null, $email = null)
    {
        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $email]
        );
    }

    // The password reset
    public function resetPassword(Request $request)
    {
        $messages = [
            'email.required' => 'Informe um e-mail.',
            'email.string' => 'Informe um e-mail válido.',
            'email.email' => 'Informe um e-mail válido.',
            'email.max' => 'O e-mail não deve conter mais de 150 caracteres.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve conter no mínimo 8 caracteres.',
            'password.max' => 'A senha deve conter no máximo 20 caracteres.',
            'password.confirmed' => 'As senhas não correspondem.',
        ];

        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email|max:150',
            'password' => 'required|confirmed|min:8|max:20',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }

        $userExists = User::findUserByEmail($request->email);
        if(!$userExists){
            return redirect()->back()->withErrors(['email' => 'E-mail inexistente na base de dados']);
        }

        $connection = DB::connection('vpOnboard');

        $record = $connection->table('password_resets')->where('email', $request->email)->first();

        if (!$record || $request->token != $record->token) {
            return back()->withErrors(['email' => 'O token expirou.']);
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();
        
            // Delete the password reset record
            $connection->table('password_resets')->where('email', $request->email)->delete();
        
            // Fire the password reset event
            event(new PasswordReset($user));
        
            // Optionally, log the user in
            //Auth::login($user);
        
            // Redirect to the login page with a success message
            return redirect()->route('loginURL')->with('success', 'Senha modificada. Prossiga com seu login.');
        } else {
            return back()->withErrors(['email' => 'Não foi possível processar sua solicitação. Tente novamente mais tarde.']);
        }
    }



}
