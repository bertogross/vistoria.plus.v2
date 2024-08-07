<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserConnections;

class SettingsConnectionsController extends Controller
{
    public function index()
    {
        $hostConnections = getHostConnections();

        return view('settings.connections', compact('hostConnections'));
    }


}
