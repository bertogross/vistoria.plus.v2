<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Survey;
use App\Models\SurveyStep;
use App\Models\SurveyTopic;
use Illuminate\Http\Request;
use App\Models\SurveyResponse;
use App\Models\SurveyTemplates;
use App\Models\SurveyAssignments;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SurveysAssignmentsController extends Controller
{
    public function show(Request $request, $assignmentId = null)
    {
        //Cache::flush();

        if (!$assignmentId) {
            abort(404);
        }

        $assignmentData = SurveyAssignments::findOrFail($assignmentId) ?? null;

        $surveyId = $assignmentData->survey_id;

        $surveyData = Survey::findOrFail($surveyId);

        $reorderingData = SurveyTemplates::reorderingData($surveyData);
        $templateData = $reorderingData ?? null;

        $stepsWithTopics = SurveyStep::with(['topics' => function($query) {
                $query->orderBy('topic_order');
            }])
            ->where('survey_id', $surveyId)
            ->orderBy('step_order')
            ->get()
            ->map(function ($step) {
                return [
                    'id' => $step->id,
                    'survey_id' => $step->survey_id,
                    'step_id' => $step->id,
                    'step_order' => $step->step_order,
                    'term_id' => $step->term_id,
                    'topics' => $step->topics->map(function ($topic) {
                        return [
                            'topic_id' => $topic->id,
                            'question' => $topic->question
                        ];
                    })
                ];
            });
        $stepsWithTopics = $stepsWithTopics ? json_decode($stepsWithTopics, true) : null;

        $analyticTermsData = Survey::fetchAndTransformSurveyDataByTerms($surveyId, $assignmentId);

        /**
         * START get terms
         */
        $terms = [];
        /*$departmentsQuery = DB::connection('vpAppTemplate')
            ->table('wlsm_departments')
            ->get()
            ->toArray();
        foreach ($departmentsQuery as $department) {
            $terms[$department->department_id] = [
                'name' => strtoupper($department->department_alias),
            ];
        }*/
        $wharehouseTermsQuery = DB::connection('vpWarehouse')
            ->table('survey_terms')
            ->get()
            ->toArray();
        foreach ($wharehouseTermsQuery as $term) {
            $terms[$term->id] = [
                'name' => strtoupper($term->name),
            ];
        }

        $customTermsQuery = DB::connection('vpAppTemplate')
            ->table('survey_terms')
            ->get()
            ->toArray();
        foreach ($customTermsQuery as $term) {
            $terms[$term->id] = [
                'name' => strtoupper($term->name),
            ];
        }
        /**
         * END get terms
         */

        $user = auth()->user();

        return view('surveys.assignment.show', compact(
            'surveyData',
            'templateData',
            'assignmentData',
            'stepsWithTopics',
            'analyticTermsData',
            'terms',
        ) );
    }

    public function formSurveyorAssignment(Request $request, $assignmentId)
    {
        if (!$assignmentId) {
            abort(404);
        }

        $currentUserId = auth()->id();

        $assignmentData = SurveyAssignments::findOrFail($assignmentId) ?? null;

        $surveyorId = $assignmentData->surveyor_id;
        $auditorId = $assignmentData->auditor_id;

        $surveyId = $assignmentData->survey_id;

        $companyId = $assignmentData->company_id;

        $surveyData = Survey::findOrFail($surveyId);

        $reorderingData = SurveyTemplates::reorderingData($surveyData);
        $templateData = $reorderingData;

        $stepsWithTopics = SurveyStep::with(['topics' => function($query) {
                $query->orderBy('topic_order');
            }])
            ->where('survey_id', $surveyId)
            ->orderBy('step_order')
            ->get()
            ->map(function ($step) {
                return [
                    'id' => $step->id,
                    'survey_id' => $step->survey_id,
                    'step_id' => $step->id,
                    'step_order' => $step->step_order,
                    'term_id' => $step->term_id,
                    'topics' => $step->topics->map(function ($topic) {
                        return [
                            'topic_id' => $topic->id,
                            'question' => $topic->question
                        ];
                    })
                ];
            });
        $stepsWithTopics = $stepsWithTopics ? json_decode($stepsWithTopics, true) : null;

        $countTopics = SurveyTopic::countSurveyTopics($surveyId);

        $countResponses = SurveyResponse::countSurveySurveyorResponses($surveyorId, $surveyId, $assignmentId);

        $percentage = $countResponses > 0 ? ($countResponses / $countTopics) * 100 : 0;
        $percentage = number_format($percentage, 0);

        return view('surveys.assignment.form-surveyor', compact(
            'surveyData',
            'templateData',
            'assignmentData',
            'stepsWithTopics',
            'percentage'
        ));
    }

    public function formAuditorAssignment(Request $request, $assignmentId)
    {
        if (!$assignmentId) {
            abort(404);
        }

        $currentUserId = auth()->id();

        $assignmentData = SurveyAssignments::findOrFail($assignmentId) ?? null;

        $surveyId = $assignmentData->survey_id;
        $companyId = $assignmentData->company_id;

        $surveyorId = $assignmentData->surveyor_id;
        $auditorId = $assignmentData->auditor_id;

        $surveyData = Survey::findOrFail($surveyId);

        $reorderingData = SurveyTemplates::reorderingData($surveyData);
        $templateData = $reorderingData;

        $stepsWithTopics = SurveyStep::with(['topics' => function($query) {
                $query->orderBy('topic_order');
            }])
            ->where('survey_id', $surveyId)
            ->orderBy('step_order')
            ->get()
            ->map(function ($step) {
                return [
                    'id' => $step->id,
                    'survey_id' => $step->survey_id,
                    'step_id' => $step->id,
                    'step_order' => $step->step_order,
                    'term_id' => $step->term_id,
                    'topics' => $step->topics->map(function ($topic) {
                        return [
                            'topic_id' => $topic->id,
                            'question' => $topic->question
                        ];
                    })
                ];
            });
        $stepsWithTopics = $stepsWithTopics ? json_decode($stepsWithTopics, true) : null;

        $countTopics = SurveyTopic::countSurveyTopics($surveyId);

        $countResponses = SurveyResponse::countSurveyAuditorResponses($auditorId, $surveyId, $assignmentId);

        $percentage = $countResponses > 0 ? ($countResponses / $countTopics) * 100 : 0;
        $percentage = number_format($percentage, 0);

        return view('surveys.assignment.form-auditor', compact(
            'surveyData',
            'templateData',
            'assignmentData',
            'stepsWithTopics',
            'percentage'
        ));
    }

    public function changeAssignmentSurveyorStatus(Request $request)
    {
        $currentUserId = auth()->id();

        $assignmentId = $request->input('assignment_id');
        $data = SurveyAssignments::findOrFail($assignmentId);

        if ($currentUserId != $data->surveyor_id) {
            return response()->json(['success' => false, 'message' => 'Você não possui autorização para prosseguir com a tarefa delegada a outra pessoa']);
        }

        $currentStatus = $data->surveyor_status;

        if($currentStatus == 'auditing'){
            return response()->json([
                'success' => false,
                'message' => 'Esta Tarefa já foi finalizada e não poderá ser editada.',
            ]);
        }
        if($currentStatus == 'losted' ){
            return response()->json([
                'success' => false,
                'message' => 'Esta Tarefa foi perdida pois o prazo expirou e por isso não poderá mais ser editada',
            ]);
        }

        if($currentStatus == 'new'){
            // [if currentStatus is new] Change to pending.
            $newStatus = 'pending';

            $message = 'Formulário gerado com sucesso';
        }elseif($currentStatus == 'in_progress'){
            // [if currentStatus is in_progress] Change to auditing.
            //$newStatus = 'auditing';
            $newStatus = 'completed';

            $message = 'Dados gravados';
        }else{
            $message = 'Status inalterado';

            $newStatus = $currentStatus;
        }

        SurveyAssignments::changeSurveyorAssignmentStatus($assignmentId, $newStatus);

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function changeAssignmentAuditorStatus(Request $request)
    {
        $currentUserId = auth()->id();

        $assignmentId = $request->input('assignment_id');
        $data = SurveyAssignments::findOrFail($assignmentId);

        if ($currentUserId != $data->auditor_id) {
            return response()->json(['success' => false, 'message' => 'Você não possui autorização para prosseguir com a tarefa delegada a outra pessoa']);
        }

        $currentStatus = $data->auditor_status;

        if($currentStatus == 'completed'){
            return response()->json([
                'success' => false,
                'message' => 'Esta Tarefa já foi finalizada não poderá mais ser editada.',
            ]);
        }
        if($currentStatus == 'losted' ){
            return response()->json([
                'success' => false,
                'message' => 'Esta Auditoria foi perdida pois o prazo expirou e por isso não poderá mais ser editada',
            ]);
        }

        if($currentStatus == 'new'){
            // [if currentStatus is new] Change to pending.
            $newStatus = 'pending';

            $message = 'Formulário gerado com sucesso';
        }
        elseif($currentStatus == 'in_progress'){
            // [if currentStatus is in_progress] Change to completed.
            $newStatus = 'completed';

            $message = 'Tarefa finalizada';
        }else{
            $message = 'Status inalterado';

            $newStatus = $currentStatus;
        }

        // Change auditor_status. So... if newStatus was 'completed', change the surveyor_status to
        SurveyAssignments::changeAuditorAssignmentStatus($assignmentId, $newStatus);

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function enterAssignmentAuditor(Request $request)
    {
        $currentUserId = auth()->id();

        $currentConnectionId = getCurrentConnectionByUserId($currentUserId);

        if($currentConnectionId == $currentUserId){
            $role = 1;
        }else{
            $role = 3;
        }

        if( !in_array($role, [1, 2]) ){
            return response()->json(['success' => false, 'message' => 'Você não possui em suas credenciais autorização para realizar Auditoria']);
        }

        $assignmentId = $request->input('assignment_id');
        $data = SurveyAssignments::findOrFail($assignmentId);

        $surveyorId = $data->surveyor_id;
        $surveyorStatus = $data->surveyor_status;

        if( $currentUserId == $surveyorId ){
            return response()->json(['success' => false, 'message' => 'Você não poderá Auditar uma Vistoria outrora realizada por você.']);
        }

        // Check if this assignment have another user audito
        if ($data->auditor_id && $currentUserId != $data->auditor_id) {
            $getUserData = getUserData($data->auditor_id);

            return response()->json(['success' => false, 'message' => 'A tarefa de Auditoria já foi delegada a outra pessoa. Solicite ao usuário '.$getUserData->name.' que a revogue para então você poder proceder com esta requisição.', 'action' => 'request']);
        }

        // Check if this assignment user auditor is the current user
        if ($data->auditor_id && $currentUserId == $data->auditor_id) {
            $message = 'Esta tarefa de Auditoria é sua.<br><br>Qual ação deseja executar?';
            $message .= '<div class="text-warning mt-2"><strong>ATENÇÃO</strong><br>Se você optar por <strong>Revogar</strong>, todas as ações já realizadas serão definitivamente deletadas sem a possibilidade de recuperação.</div>';

            return response()->json(['success' => false, 'message' => $message, 'action' => 'choice', 'current_surveyor_status' => $surveyorStatus]);
        }

        // Update auditor assignment
        $columns['auditor_status'] = 'new';
        $columns['auditor_id'] = $currentUserId;
        $data->update($columns);

        $message = 'A Auditoria desta tarefa foi requisitada.';

        return response()->json(['success' => true, 'message' => $message, 'current_surveyor_status' => $surveyorStatus]);
    }

    /*
    // TODO
    public function requestAssignmentAuditor(Request $request, $assignmentId)
    {
        $currentUserId = auth()->id();

        $data = SurveyAssignments::findOrFail($assignmentId);

        $currentAuditorId = $data->auditor_id;

        if($currentUserId == $currentAuditorId){
            return response()->json(['success' => false, 'message' => 'Esta tarefa de Auditoria já é sua']);
        }

        // TODO send message for $currentAuditorId to release this Assignment and transfer to antoher user

        return response()->json(['success' => false, 'message' => 'Solicitação enviada']);
    }
    */

    public function revokeAssignmentAuditor(Request $request, $assignmentId)
    {
        $currentUserId = auth()->id();

        $data = SurveyAssignments::findOrFail($assignmentId);

        $currentAuditorId = $data->auditor_id;

        if($currentUserId != $currentAuditorId){
            return response()->json(['success' => false, 'message' => 'Você não pode revogar a tarefa de outro usuário']);
        }

        $columns['surveyor_status'] = 'completed';
        $columns['auditor_status'] = null;
        $columns['auditor_id'] = null;
        $data->update($columns);

        return response()->json(['success' => true, 'message' => 'Auditoria Revogada']);
    }

    public function requestAssignmentActivities(Request $request, $subDays = null)
    {
        $error = response()->json(['success' => false, 'message' => 'Ainda não há dados']);

        $countSurvey = Survey::count();
        $countSurveyAssignments = SurveyAssignments::count();
        if(!$countSurvey || !$countSurveyAssignments){
            return $error;
        }

        //$subDays = $request->subDays ? intval($request->subDays) : null;

        if(!$subDays){
            $days = Carbon::now()->subDays(7);
        }else{
            $days = Carbon::now()->subDays($subDays);
        }

        $surveyorArrStatus = ['new', 'pending', 'in_progress', 'auditing', 'completed'];

        $auditorArrStatus = ['pending', 'in_progress', 'completed']; //'waiting',

        // Fetching surveyor and auditor assignments
        /*$assignments = SurveyAssignments::whereIn('surveyor_status', $surveyorArrStatus)
            ->orWhereIn('auditor_status', $auditorArrStatus)
            ->whereDate('created_at', '>=', $days)
            ->orderBy('updated_at', 'desc')
            ->limit(100)
            ->get();*/
        $assignments = SurveyAssignments::where(function ($query) use ($surveyorArrStatus, $auditorArrStatus) {
                $query->whereIn('surveyor_status', $surveyorArrStatus)
                      ->orWhereIn('auditor_status', $auditorArrStatus);
            })
            ->whereDate('created_at', '>=', $days)
            ->orderBy('updated_at', 'desc')
            ->limit(100)
            ->get();

        if ($assignments->isEmpty()) {
            return $error;
        }

        $activities = [];

        // Process assignments
        foreach ($assignments as $assignment) {
            if (in_array($assignment->surveyor_status, $surveyorArrStatus)) {
                $activities[] = $this->processAssignment($assignment, 'surveyor');
            }
            if (in_array($assignment->auditor_status, $auditorArrStatus)) {
                $activities[] = $this->processAssignment($assignment, 'auditor');
            }
        }

        $activities = array_filter($activities);

        if (!empty($activities)) {
            return response()->json(['success' => true, 'activities' => $activities]);
        } else {
            return $error;
        }
    }

    public function requestAssignments(Request $request, $subDays = null)
    {
        $error = response()->json(['success' => false, 'message' => 'Ainda não há dados']);

        $countSurvey = Survey::count();
        $countSurveyAssignments = SurveyAssignments::count();
        if(!$countSurvey || !$countSurveyAssignments){
            return $error;
        }

        //$subDays = $request->subDays ? intval($request->subDays) : null;

        if(!$subDays){
            $days = Carbon::now()->subDays(7);
        }else{
            $days = Carbon::now()->subDays($subDays);
        }

        $surveyorArrStatus = ['new', 'pending', 'in_progress', 'auditing', 'completed'];

        $auditorArrStatus = ['pending', 'in_progress', 'completed']; //'waiting',

        // Fetching surveyor and auditor assignments
        /*$assignments = SurveyAssignments::whereIn('surveyor_status', $surveyorArrStatus)
            ->orWhereIn('auditor_status', $auditorArrStatus)
            ->whereDate('created_at', '>=', $days)
            ->orderBy('updated_at', 'desc')
            ->limit(100)
            ->get();*/
        $assignments = SurveyAssignments::where(function ($query) use ($surveyorArrStatus, $auditorArrStatus) {
                $query->whereIn('surveyor_status', $surveyorArrStatus)
                      ->orWhereIn('auditor_status', $auditorArrStatus);
            })
            ->whereDate('created_at', '>=', $days)
            ->orderBy('updated_at', 'desc')
            ->limit(100)
            ->get();

        if ($assignments->isEmpty()) {
            return $error;
        }

        $activities = [];

        // Process assignments
        foreach ($assignments as $assignment) {
            if (in_array($assignment->surveyor_status, $surveyorArrStatus)) {
                $activities[] = $this->processAssignment($assignment, 'surveyor');
            }
            if (in_array($assignment->auditor_status, $auditorArrStatus)) {
                $activities[] = $this->processAssignment($assignment, 'auditor');
            }
        }

        $activities = array_filter($activities);

        if (!empty($activities)) {
            return response()->json(['success' => true, 'activities' => $activities]);
        } else {
            return $error;
        }
    }

    private function processAssignment($assignment, $designated)
    {
        $assignmentId = $assignment->id;

        $surveyId = $assignment->survey_id;

        $survey = Survey::find($surveyId);
        if(!$survey){
            return false;
        }
        $surveyTitle = $survey->title;

        $templateName = getSurveyTemplateNameById($survey->template_id);

        $companyId = $assignment->company_id;
        $companyName = getCompanyNameById($companyId);

        $surveyorId = $assignment->surveyor_id ?? null;
        $auditorId = $assignment->auditor_id ?? null;

        $assignmentStatus = $assignment->{$designated . '_status'} ?? null;

        $percentage = SurveyAssignments::calculateSurveyPercentage($surveyId, $companyId, $assignmentId, $surveyorId, $auditorId, $designated);
        $progressBarClass = getProgressBarClass($percentage);

        $label = $designated == 'surveyor' ? '<span class="badge bg-dark-subtle text-body badge-border mb-2 ms-2" data-survey-id="'.$surveyId.'">Vistoria</span>' : '<span class="badge bg-dark-subtle text-secondary badge-border mb-2 ms-2" data-survey-id="'.$surveyId.'">Auditoria</span>';

        if($designated == 'auditor'){
            $designatedUserId = $auditorId;
        }elseif($designated == 'surveyor'){
            $designatedUserId = $surveyorId;
        }

        $getUserData = getUserData($designatedUserId);

        $designatedUserName = $getUserData ? limitChars($getUserData->name, 20) : '';
        $designatedUserAvatar = $getUserData ? $getUserData->avatar : '';
        $designatedUserProfileURL = route('profileShowURL', $designatedUserId);

        return [
            'assignmentId' => $assignmentId,
            'surveyId' => $surveyId,
            'surveyTitle' => limitChars($surveyTitle, 40),
            'companyId' => $companyId,
            'companyName' => limitChars($companyName, 20),
            'templateName' => limitChars($templateName, 40),
            'assignmentStatus' => $assignmentStatus,
            'designatedUserId' => $designatedUserId,
            'designatedUserName' => $designatedUserName,
            'designatedUserAvatar' => checkUserAvatar($designatedUserAvatar),
            'designatedUserProfileURL' => $designatedUserProfileURL,
            'label' => $label,
            'percentage' => $percentage,
            'progressBarClass' => $progressBarClass,
            'createddAt' => $assignment->created_at->format('d/m/Y'),
            'updatedAt' => $assignment->updated_at->format('d/m/Y')
        ];
    }




}
