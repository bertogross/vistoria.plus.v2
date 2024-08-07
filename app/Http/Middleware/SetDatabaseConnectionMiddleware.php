<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
//use App\Providers\DynamicDatabaseServiceProvider;

class SetDatabaseConnectionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //DynamicDatabaseServiceProvider::setDynamicDatabaseConnection();

        if (auth()->check()) {
            $userId = auth()->id();
            $currentConnectionId = getCurrentConnectionByUserId($userId);

            if ($currentConnectionId) {

                $databaseName = 'vpApp' . $currentConnectionId;

                if (DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$databaseName])) {
                    config(['database.connections.vpAppTemplate.database' => $databaseName]);
                }
            }
        }

        return $next($request);
    }
}
