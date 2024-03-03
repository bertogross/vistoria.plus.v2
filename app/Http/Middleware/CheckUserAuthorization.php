<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\UserConnections;

class CheckUserAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (!UserConnections::preventUnauthorizedConnection()) {
            // If unauthorized, redirect to a specific route or return an error response
            return redirect('errors.unauthorized')->with('error', 'Você não possui autorização para acessar esta conexão');
        }

        return $next($request);
    }
}
