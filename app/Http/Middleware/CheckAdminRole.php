<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserMeta;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Providers\RouteServiceProvider;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        if (!auth()->check()) {
            return redirect('/'); // Redirect if not authenticated
        }

        $user = auth()->user();

        // Get the current connection ID from user meta
        $currentConnectionId = getCurrentConnectionByUserId($user->id);

        // Check if the user's ID matches the current connection ID
        // Redirect if trying to access settings and the IDs don't match
        if ($request->is('settings/*') && $user->id != intval($currentConnectionId)) {
            //return redirect('/');
            return redirect(RouteServiceProvider::HOME);
        }

        // Check if the user has an admin role
        if ($user->hasRole(User::ROLE_ADMIN)) {
            return $next($request);
        }

        return $next($request);
    }
}
