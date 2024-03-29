<?php

namespace App\Http\Controllers;

//use App\Models\User;
use App\Models\Survey;
use App\Models\UserConnections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{

    public function index()
    {
        //$users = User::all();

        // Usefull if crontab or Kernel schedule is losted
        // D:public_html\app\Console\Commands
        Survey::processSurveys();

        $users = getUsers();

        return view('team.index', compact('users'));
    }

}
