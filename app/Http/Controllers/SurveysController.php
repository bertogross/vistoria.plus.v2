<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\UserConnections;
//use App\Models\SurveyCompose;
use App\Models\Survey;
use App\Models\SurveyStep;
//use App\Models\SurveyMeta;
use App\Models\SurveyTerms;
use App\Models\SurveyTopic;
use Illuminate\Http\Request;
use App\Models\SurveyResponse;
use App\Models\SurveyTemplates;
use App\Models\SurveyAssignments;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Collection;

class SurveysController extends Controller
{
    public function index(Request $request)
    {
        Cache::flush();

        $currentUserId = auth()->id();

        // Usefull if crontab or Kernel schedule is losted
        Survey::populateSurveys();

        $createdAt = $request->input('created_at');
        $status = $request->input('status');

        /**
         * START TEMPLATES QUERY
         */
        $queryTemplates = SurveyTemplates::query();
        //$queryTemplates->where('user_id', $currentUserId);
        //$queryTemplates->where('condition_of','publish');

        $templates = $queryTemplates->orderBy('created_at', 'desc')->paginate(50);
        /**
         * END TEMPLATES QUERY
         */

        /**
         * START SURVEYS QUERY
         */
        $query = Survey::query();

        if ($status) {
            $query->where('status', $status);
        }
        //$query = $query->where('user_id', $currentUserId);
        //$query = $query->where('condition_of', 'publish');

        if ($createdAt) {
            $dateRange = explode(' até ', $createdAt);
            if (is_array($dateRange) && count($dateRange) === 2) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($dateRange[0]))->startOfDay()->format('Y-m-d H:i:s');
                $endDate = Carbon::createFromFormat('d/m/Y', trim($dateRange[1]))->endOfDay()->format('Y-m-d H:i:s');

                $query->whereBetween('created_at', [$startDate, $endDate]);
            } else {
                $date = Carbon::createFromFormat('d/m/Y', $createdAt)->format('Y-m-d');

                $query->whereDate('created_at', '=', $date);
            }
        }

        $data = $query->orderBy('created_at', 'desc')->paginate(10);

        /*
        $data = $query->whereIn('status', ['new', 'scheduled', 'started', 'stopped'])->orderBy('created_at')->paginate(10);

        $dataCompleted = $query->where('status', 'completed')->orderBy('created_at')->paginate(10);

        $dataFiled = $query->where('status', 'filed')->orderBy('created_at')->paginate(10);
        */

        /**
         * END SURVEYS QUERY
         */

        $getSurveyRecurringTranslations = Survey::getSurveyRecurringTranslations();

        $getSurveyStatusTranslations = Survey::getSurveyStatusTranslations();

        $getAuthorizedCompanies = getAuthorizedCompanies($currentUserId);

        $dateRange = Survey::getSurveysDateRange();
        $firstDate = $dateRange['first_date'] ?? null;
        $lastDate = $dateRange['last_date'] ?? null;

        return view('surveys.index', compact(
            'data',
            'templates',
            'getSurveyRecurringTranslations',
            'getSurveyStatusTranslations',
            'getAuthorizedCompanies',
            'firstDate',
            'lastDate',
        ));
    }

    public function show(Request $request, $id = null)
    {
        Cache::flush();

        $swapData = getCookie('surveys-swap');

        if (!$id) {
            abort(404);
        }

        $data = Survey::findOrFail($id);

        $surveyId = $data->id;

        //$analyticCompaniesData = Survey::fetchAndTransformSurveyDataByCompanies($surveyId);

        $analyticTermsData = Survey::fetchAndTransformSurveyDataByTerms($surveyId);

        $surveyAssignmentData = SurveyAssignments::getSurveyAssignmentBySurveyId($surveyId);

        $getSurveyAssignmentStatusTranslations = SurveyAssignments::getSurveyAssignmentStatusTranslations();

        $users = getUsers();
        $userAvatars = [];
        foreach ($users as $user) {
            $userAvatars[$user->id] = [
                'name' => $user->name,
                'avatar' => getUserData($user->id)->avatar,
            ];
        }

        $getCompanies = getActiveCompanies();
        $companies = [];
        foreach ($getCompanies as $company) {
            $companies[$company->id] = [
                'name' => $company->name,
            ];
        }

        /*$stepsQuery = DB::connection('vpAppTemplate')
            ->table('survey_steps')
            ->where('survey_id', $surveyId)
            ->get()
            ->toArray();
        $steps = $stepsQuery ?? null;*/

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

        $dateRange = SurveyAssignments::getAssignmentDateRange();
        $firstDate = $dateRange['first_date'] ?? null;
        $lastDate = $dateRange['last_date'] ?? null;

        return view('surveys.show', compact(
            'data',
            //'analyticCompaniesData',
            'analyticTermsData',
            'surveyAssignmentData',
            'getSurveyAssignmentStatusTranslations',
            'companies',
            'terms',
            //'steps',
            'userAvatars',
            'firstDate',
            'lastDate',
            'swapData'
        ) );
    }

    // Add
    public function create()
    {
        // Cache::flush();
        session()->forget('success');

        $currentUserId = auth()->id();

        $data = null;

        $selectedCompanies = [];

        $distributedData = null;

        $users = getUsers();

        $getActiveCompanies = getActiveCompanies();
        $getAuthorizedCompanies = getAuthorizedCompanies($currentUserId);
        $getSurveyRecurringTranslations = Survey::getSurveyRecurringTranslations();

        $queryTemplates = SurveyTemplates::query();
        //$queryTemplates->where('user_id', $currentUserId);

        $templates = $queryTemplates->orderBy('title')
            ->where('condition_of', 'publish')
            ->get();// ->paginate(50)

        $countAllResponses = $countTodayResponses = 0;

        return view('surveys.create', compact(
                'data',
                'distributedData',
                'selectedCompanies',
                'templates',
                'users',
                'getActiveCompanies',
                'getAuthorizedCompanies',
                'getSurveyRecurringTranslations',
                'countAllResponses',
                'countTodayResponses'
            )
        );
    }

    public function edit(Request $request, $id = null)
    {
        // Cache::flush();
        session()->forget('success');

        $currentUserId = auth()->id();

        if (!$id) {
            abort(404);
        }

        $data = Survey::findOrFail($id);
        /*
        $data = Survey::query();
        $data->where('id', $id);
        $data->where('user_id', $currentUserId);
        */
        $distributedData = $data->distributed_data ?? null;
            $distributedData = $distributedData ? json_decode($distributedData, true) : '';

        $selectedCompanies = $data->companies ?? '';
            $selectedCompanies = $selectedCompanies ? json_decode($selectedCompanies, true) : [];

        // Check if the current user is the creator
        if ($currentUserId != $data->user_id) {
            return response()->json(['success' => false, 'message' => 'Você não possui autorização para editar um registro gerado por outra pessoa']);
        }

        $users = getUsers();

        $getActiveCompanies = getActiveCompanies();
        $getAuthorizedCompanies = getAuthorizedCompanies($currentUserId);
        $getSurveyRecurringTranslations = Survey::getSurveyRecurringTranslations();

        $queryTemplates = SurveyTemplates::query();
        //$queryTemplates->where('user_id', $currentUserId);
        $templates = $queryTemplates->orderBy('title')->get();// ->paginate(50)

        $countAllResponses = Survey::countSurveyAllResponses($id);
        $countTodayResponses = Survey::countSurveyAllResponsesFromToday($id);

        return view('surveys.edit', compact(
                'data',
                'distributedData',
                'selectedCompanies',
                'templates',
                'users',
                'getActiveCompanies',
                'getAuthorizedCompanies',
                'getSurveyRecurringTranslations',
                'countAllResponses',
                'countTodayResponses'
            )
        );
    }

    public function storeOrUpdate(Request $request, $id = null)
    {
        $currentUserId = auth()->id();

        // Get today's date in 'Ymd' format
        $today = Carbon::now()->startOfDay();

        $getActiveCompanies = getActiveCompanies();

        /*$companyIds = $getActiveCompanies ? $getActiveCompanies : null;
        $companyIds = $companyIds ? array_column($companyIds, 'id') : [1];
        $companyIds = $companyIds && count($companyIds) > 1 ? array_unique($companyIds) : $companyIds;*/

        $getActiveCompanies = getActiveCompanies();
        $companyIds = $getActiveCompanies ? array_column($getActiveCompanies, 'id') : [1];
        $companyIds = array_unique($companyIds);
        $companyIds = array_map('strval', $companyIds);

        // \Log::info('Company IDs: ', $companyIds);
        // \Log::info('Request Data: ', $request->all());

        $messages = [
            'title.required' => 'Necessário Inserir um Título',
            'template_id.required' => 'Necessário selecionar um modelo',
            'distributed_data.required' => 'Necessário delegar usuários para Checklist e Auditoria',
            'recurring.required' => 'Escolha a recorrência',
            'companies.required' => 'Selecione uma ou mais unidades',
        ];

        try {
            $validatedData = Validator::make($request->all(), [
                'title' => 'required|string|max:191',
                //'recurring' => 'required|in:once,daily,weekly,biweekly,monthly,annual',
                //'description' => 'nullable|string|max:1000',
                'template_id' => 'required',
                //'auditor_id' => 'required',
                'distributed_data' => 'required',
                'recurring' => 'required|in:once,daily,weekly,biweekly,monthly,annual',
                //'companies' => 'required|in:'.implode(',', $companyIds).'',
                'companies' => 'required|array',
                'companies.*' => 'in:'.implode(',', $companyIds),
            ], $messages)->validate();
        } catch (ValidationException $e) {
            $errors = $e->errors();

            $errorMessages = '';
            foreach ($errors as $field => $messages) {
                foreach ($messages as $message) {
                    $errorMessages = $message;
                    break;
                }
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessages
            ]);
        }

        // Convert array inputs to JSON strings for storage
        $validatedData = array_map(function ($value) {
            return is_array($value) ? json_encode($value) : $value;
        }, $validatedData);

        $jsonString = $request->input('distributed_data');
        $distributedData = json_decode($jsonString);

        if (json_last_error() !== JSON_ERROR_NONE || !is_object($distributedData)) {
            return response()->json(['success' => false, 'message' => 'Invalid JSON format']);
        }

        $distributedData = $validatedData['distributed_data'];

        $templateId = $validatedData['template_id'];
        $templateQuery = SurveyTemplates::findOrFail($templateId);
        $templateData = $templateQuery->template_data ?? '';

        $validatedData['user_id'] = $currentUserId;

        $recurring = $request->input('recurring');

        $companies = $request->input('companies', []);
        $companies = array_map('intval', $companies);
        $companies = json_encode($companies);

        $startAt = $request->input('start_at');
        $startAt = !empty($startAt) ? Carbon::createFromFormat('d/m/Y', trim($startAt))->startOfDay()->format('Y-m-d H:i:s') : null;

        // Parse the provided start date and set it to the start of the day
        if($startAt && $startAt > $today){
            $validatedData['status'] = 'scheduled';
        }

        $endIn = $request->input('end_in');
        $endIn = !empty($endIn) ? Carbon::createFromFormat('d/m/Y', trim($endIn))->endOfDay()->format('Y-m-d H:i:s') : null;

        if($endIn && $startAt && $endIn <= $startAt){
            $validatedData['end_in'] = $startAt ? Carbon::createFromFormat('Y-m-d H:i:s', trim($startAt))->endOfDay()->format('Y-m-d H:i:s') : null;
        }else{
            $validatedData['end_in'] = $endIn && $endIn < $today ? $today : $endIn;
        }

        if($recurring == 'once'){
            $validatedData['end_in'] = Carbon::createFromFormat('Y-m-d H:i:s', trim($startAt))->endOfDay()->format('Y-m-d H:i:s');
        }

        if(!$startAt){
            $validatedData['start_at'] = null;
            $validatedData['end_in'] = null;
            $validatedData['status'] = 'new';
        }

        if ($id) {
            // Update operation
            $survey = Survey::findOrFail($id);

            $surveyId = $survey->id;

            // Prevent from cracker to change if Responses is populated
            $countResponses = Survey::countSurveyAllResponses($id);
            if($countResponses > 0){
                $validatedData['companies'] = $survey->companies;
                $validatedData['recurring'] = $survey->recurring;
            }else{
                $validatedData['companies'] = $companies;
            }

            $surveyStatus = $survey->status;

            if( in_array($surveyStatus, ['new', 'scheduled']) ){
                // Authorize template overwrite the existent
                $validatedData['template_data'] = $templateData;
            }

            if ( in_array($surveyStatus, ['started', 'stopped', 'completed', 'filed']) ){
                // Prevent user change existent start_at date from form
                $validatedData['start_at'] = $survey->start_at ?? $startAt;
                $validatedData['end_in'] = $endIn ?? null;
            }else{
                $validatedData['start_at'] = $startAt ?? null;
            }

            // Check if the current user is the creator
            if ($currentUserId != $survey->user_id) {
                return response()->json(['success' => false, 'message' => 'Você não possui autorização para editar um registro gerado por outra pessoa']);
            }

            $survey->update($validatedData);

            // Update from model if task is not started
            if(in_array($surveyStatus, ['new', 'scheduled', 'started'])){
                SurveyStep::populateSurveySteps($templateId, $surveyId);
            }

            // Start the task by distributing to each party
            SurveyAssignments::distributingAssignments($surveyId);

            return response()->json([
                'success' => true,
                'message' => 'Dados atualizados!',
                'id' => $surveyId
            ]);
        } else {
            // If the user takes too long to fill out the form and the server has already turned the day around
            if($startAt && $startAt < $today){
                return response()->json(['success' => false, 'message' => 'A data Inicial não deve ser inferior a hoje: '.$today->format('d/m/Y').'']);
            }

            // Store operation
            $validatedData['template_data'] = $templateData;

            $validatedData['companies'] = $companies;

            $validatedData['start_at'] = $startAt;

            $survey = new Survey;
            $survey->fill($validatedData);
            $survey->save();

            $surveyId = $survey->id;

            SurveyStep::populateSurveySteps($templateId, $surveyId);

            return response()->json([
                'success' => true,
                'message' => 'Dados adicionados com sucesso!',
                'id' => $surveyId
            ]);
        }
    }

    //start/stop survey
    public function changeStatus(Request $request)
    {
        $currentUserId = auth()->id();

        $now = now()->format('Y-m-d H:i:s');

        $surveyId = $request->input('id');
        $surveyId = intval($surveyId);

        $survey = Survey::findOrFail($surveyId);

        $currentStatus = $survey->status;

        $startAt = $survey->start_at;

        $templateId = $survey->template_id;

        if($currentStatus == 'new'){
            SurveyStep::populateSurveySteps($templateId, $surveyId);
        }

        if ($currentUserId != $survey->user_id) {
            return response()->json(['success' => false, 'message' => 'Você não possui autorização para Inicializar/Interromper a rotina registrada por outra pessoa']);
        }

        $countResponses = Survey::countSurveyAllResponsesFromToday($surveyId);

        if($countResponses > 0){
            // TODO URGENT
            // chek if it is necessary because when task is in progress I can stop
            //return response()->json(['success' => false, 'message' => 'Não será possível interromper esta tarefa pois dados já estão sendo coletados.']);
        }

        //$oldStatus = $survey->old_status ?? $currentStatus;

        //$message = 'Status da tarefa foi atualizado com sucesso';
        //$newStatus = $oldStatus;

        switch ($currentStatus) {
            case 'new':
                $newStatus = 'started';

                if(!$startAt){
                    $columns['start_at'] = $now ;
                }

                $message = 'Rotina inicializada com sucesso';

                // Start the task by distributing to each party
                SurveyAssignments::distributingAssignments($surveyId);
                break;
            case 'stopped':
                $newStatus = 'started';

                if(!$startAt){
                    $columns['start_at'] = $now;
                }

                $message = 'Rotina reinicializada com sucesso';

                // Restart the task by distributing to each party
                SurveyAssignments::distributingAssignments($surveyId);
                break;
            case 'started':
                $newStatus = 'stopped';

                SurveyAssignments::removeDistributingAssignments($surveyId);

                $message = 'Rotina interrompida';
                break;
        }

        $columns['status'] = $newStatus;
        //$columns['old_status'] = $currentStatus;

        // Save the changes
        $survey->update($columns);

        $message = $newStatus == 'stopped' ? 'Checklist interrompidO' : $message;

        // Return a success response
        return response()->json(['success' => true, 'message' => $message]);
    }

    public function surveyReloadUsersTab(Request $request, $id = null)
    {
        if ($id) {
            $data = Survey::findOrFail($id);
        }else{
            $data = null;
        }

        $distributedData = $data->distributed_data ?? null;
            $distributedData = $distributedData ? json_decode($distributedData, true) : '';

        $getActiveCompanies = getActiveCompanies();
        $users = getUsers();

        $selectedCompanies = $data->companies ?? '';
            $selectedCompanies = $selectedCompanies ? json_decode($selectedCompanies, true) : [];

        return view('surveys.layouts.create-users-tab', compact(
                'data',
                'distributedData',
                'selectedCompanies',
                'getActiveCompanies',
                'users',
            )
        );
    }


}
