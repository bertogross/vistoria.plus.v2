<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Survey;
use App\Models\UserMeta;
use Illuminate\Http\Request;
use App\Models\SurveyAssignments;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ProfileController extends Controller
{
    public function index($id = null)
    {
        //Cache::flush();

        if (!$id && auth()->check()) {
            $user = auth()->user();
        } else {
            $user = User::findOrFail($id);
        }

        // Usefull if crontab or Kernel schedule is losted
        // D:public_html\app\Console\Commands
        Survey::populateSurveys();

        $profileUserId = $user->id;

        $currentUser = auth()->user();

        $assignmentData = SurveyAssignments::where(function ($query) use ($profileUserId) {
            $query->where('surveyor_id', $profileUserId)
                ->orWhere('auditor_id', $profileUserId);
            })
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get()
            ->toArray();


        $getSurveyAssignmentStatusTranslations = SurveyAssignments::getSurveyAssignmentStatusTranslations();
        $requiredKeys = ['new', 'pending', 'in_progress', 'auditing', 'completed', 'losted'];
        $filteredStatuses = array_intersect_key($getSurveyAssignmentStatusTranslations, array_flip($requiredKeys));

        return view('profile.index', compact(
            'user',
            'profileUserId',
            //'profileRoleName',
            'assignmentData',
            'filteredStatuses',
        ));

    }

    public function settings()
    {
        // Ensure the user is authenticated
        if (!Auth::check()) {
            return redirect('/login'); // or wherever you want to redirect unauthenticated users
        }

        // Check if the authenticated user has ROLE_ADMIN
        if (Auth::user()->hasRole(User::ROLE_ADMIN)) {
            return redirect('/settings/account'); // Redirect admins to settings/account
        }

        // Load the view
        return view('profile/settings');
    }

    public function changeLayoutMode(Request $request){
        try{
            $currentUserId = auth()->id();

            $theme = $request->json('theme');

            UserMeta::updateOrCreate(
                ['user_id' => $currentUserId, 'meta_key' => 'theme'],
                ['meta_value' => $theme]
            );

            return response()->json([
                'success' => true,
                'message' => 'Layout modificado!'
            ]);
        } catch (\Exception $e) {
            // Log the exception details for debugging
            \Log::error('Error changing connection: ' . $e->getMessage());

            // Return a response indicating failure
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alternar Layout.'
            ], 500); // 500 Internal Server Error
        }
    }


}
