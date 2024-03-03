<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Exception;

class InvitationController extends Controller
{
    public static function invitationResponse(Request $request, $connectionCode)
    {
        try {
            $decryptedValue = Crypt::decryptString($connectionCode);
            list($currentUserId, $userId) = explode('~~~', $decryptedValue);

            //TODO check if user userId exists

            //If userId exists
            UserConnections::setConnectionData($userExists->id, $currentUserId, $userRole, 'active', $companies, $connectionCode);


            //TODO else userId not exists create a new one

            // After create a new user:
            UserConnections::setConnectionData($newUser->id, $currentUserId, $userRole, 'active', $companies, $connectionCode);


        } catch (Exception $e) {
            \Log::error('invitationResponse: ' . $e->getMessage());
        }

    }
}
