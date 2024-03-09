<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use App\Http\Controllers\OnboardController;
use App\Models\UserMeta;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('web')->attempt($credentials, $request->filled('remember'))) {
            // Check if the user's status is 0
            if (Auth::user()->status == 0) {
                // Log the user out
                Auth::logout();

                // Redirect back with an error message
                return redirect()->back()->withErrors(['email' => 'Your account is inactive. Please contact support.']);
            }

            // Update the last_login time for the user
            $user = Auth::user();
            $user->last_login = now();
            $user->save();

            // Check if database exists
            $databaseName = 'vpApp' . $user->id;
            if (!DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$databaseName])) {
                // Create user database based on vpDefaultSchema
                UserConnections::duplicateAndRenameDefaultSchema($user->id);
            }

            // Reset current connection
            UserMeta::updateUserMeta($user->id, 'current_database_connection', $user->id);

            // Authentication passed
            //return redirect()->intended('/');
            return redirect()->away(''.env('APP_URL').'');
        }

        // Handle failed authentication
        return redirect()->back()->withErrors(['email' => 'Falha de autenticação']);
    }

    public function logout(Request $request)
    {
        // Logout the user
        Auth::logout();

        //Session::forget('SM-DBN');

        // Forget cookies
        $cookie1 = Cookie::forget('SM-DBN');
        $cookie2 = Cookie::forget('vp_session');
        // Add as many cookies as you want to forget

        // Redirect to the homepage or login page with the forgotten cookies
        return redirect('/')->withCookies([$cookie1, $cookie2]);
    }


}
