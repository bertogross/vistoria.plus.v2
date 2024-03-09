<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserConnections;

class SettingsConnectionsController extends Controller
{
    public function index()
    {
        $myConnections = getUsersDataFromMyConnections();

        return view('settings.connections', compact('myConnections'));
    }


}
