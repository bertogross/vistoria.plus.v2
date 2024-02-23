<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Survey;
use App\Models\SurveyTopic;
use Illuminate\Http\Request;
use App\Models\SurveyResponse;
use App\Models\SurveyAssignments;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SurveysResponsesController extends Controller
{
    public function responsesSurveyorStoreOrUpdate(Request $request, $id = null)
    {
        $messages = [
            'id.required' => 'O campo id é obrigatório',
            'survey_id.required' => 'O campo survey_id é obrigatório',
            'step_id.required' => 'O campo step_id é obrigatório',
            'topic_id.required' => 'O campo topic_id é obrigatório',
            //'compliance_survey.required' => 'Marque: Conforme ou Não Conforme',
            //'compliance_survey.in' => 'Marque Apenas se Conforme ou Não Conforme',//'O campo compliance_survey deve ser yes, no ou na.',
        ];

        try {
            $validatedData = Validator::make($request->all(), [
                'id' => 'required',
                'survey_id' => 'required',
                'step_id' => 'required',
                'topic_id' => 'required',
                //'compliance_survey' => 'required|in:yes,no,na',
                //'comment_survey' => 'sometimes|string',
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

        try {
            // Get authenticated user ID
            $currentUserId = auth()->id();

            $assignmentId = $request->input('assignment_id');

            $assignmentData = SurveyAssignments::findOrFail($assignmentId);
            $assignmentCreatedAt = $assignmentData->created_at;

            $now = Carbon::now()->startOfDay();
            $timeLimit = $assignmentCreatedAt->endOfDay();

            $surveyId = $assignmentData->survey_id;
            $surveyData = Survey::findOrFail($surveyId);
            $surveyRecurring = $surveyData->recurring;

            if(in_array($surveyRecurring, ['once', 'daily'])){
                if ($now->gt($timeLimit)) {
                    // Change SurveyorAssignment status
                    SurveyAssignments::changeSurveyorAssignmentStatus($assignmentId, 'losted');

                    return response()->json([
                        'success' => false,
                        'message' => 'O prazo encerrou e esta tarefa foi perdida'
                    ]);
                }
            }

            // Change SurveyorAssignment status
            SurveyAssignments::changeSurveyorAssignmentStatus($assignmentId, 'in_progress');

            // Prepare data for saving
            $data = $request->only(['surveyor_id', 'step_id', 'topic_id', 'survey_id', 'assignment_id', 'company_id', 'compliance_survey', 'comment_survey', 'attachments_survey']);

            $data['surveyor_id'] = $currentUserId;

            $data['assignment_id'] = $assignmentId;

            $countTopics = SurveyTopic::countSurveyTopics($surveyId);

            // Prevent error from JavaScript if input[name="response_id"] was cracked.
            // Check if exists the response. If exist get the Id and update.
            $existingResponse = SurveyResponse::where('survey_id', $surveyId)
                ->where('assignment_id', $assignmentId)
                ->where('step_id', $data['step_id'])
                ->where('topic_id', $data['topic_id'])
                ->first();
            if($existingResponse){
                $id = $existingResponse->id;
            }

            if ($currentUserId != $assignmentData->surveyor_id) {
                return response()->json(['success' => false, 'message' => 'Você não possui autorização para prosseguir com a tarefa delegada a outra pessoa']);
            }

            $surveyorAssignmentStatus = $assignmentData->surveyor_status;
            if($surveyorAssignmentStatus == 'auditing'){
                return response()->json([
                    'success' => false,
                    'message' => 'Esta Tarefa está passando por Auditoria e não poderá ser editada',
                ]);
            }
            if($surveyorAssignmentStatus == 'losted'){
                return response()->json([
                    'success' => false,
                    'message' => 'Esta Tarefa foi perdida pois o prazo expirou e por isso não poderá mais ser editada',
                ]);
            }

            $complianceSurvey = $request->input('compliance_survey');

            $attachmentIds = $request->input('attachment_ids');

            /*
            if( $complianceSurvey == 'no' && !$attachmentIds ){
                if ($id) {
                    // Update existing survey response
                    $SurveyResponse = SurveyResponse::findOrFail($id);

                    $columns['attachments_survey'] = null;
                    $columns['compliance_survey'] = null;
                    $SurveyResponse->update($columns);
                }

                // Count the number of steps that have been finished
                $countResponses = SurveyResponse::countSurveySurveyorResponses($currentUserId, $surveyId, $assignmentId);

                return response()->json([
                    'success' => false,
                    'message' => 'Necessário enviar ao menos uma foto',
                    'action' => 'changeToPending',
                    'action2' => 'blinkPhotoButton',
                    'countResponses' => $countResponses,
                    'countTopics' => $countTopics
                ]);
            }
            */
            $attachmentIdsInt = $attachmentIds ? array_map('intval', $attachmentIds) : [];

            // Check if attachmentIdsInt is empty and handle accordingly
            $data['attachments_survey'] = !empty($attachmentIdsInt) ? json_encode($attachmentIdsInt) : null;

            $comment = $request->input('comment_survey');
            $comment = trim($comment);

            /*
            if( $complianceSurvey == 'no' && empty($comment) ){
                if ($id) {
                    // Update existing survey response
                    $SurveyResponse = SurveyResponse::findOrFail($id);

                    $columns['compliance_survey'] = null;
                    $SurveyResponse->update($columns);
                }

                // Count the number of steps that have been finished
                $countResponses = SurveyResponse::countSurveySurveyorResponses($currentUserId, $surveyId, $assignmentId);

                return response()->json([
                    'success' => false,
                    'message' => 'Necessário descrever o motivo da Não Conformidade',
                    'action' => 'changeToPending',
                    'action2' => 'showTextarea',
                    'countResponses' => $countResponses,
                    'countTopics' => $countTopics
                ]);
            }elseif($complianceSurvey == 'yes'){
                if ($id) {
                    // Update existing survey response
                    $SurveyResponse = SurveyResponse::findOrFail($id);

                    $columns['compliance_survey'] = $complianceSurvey;
                    $SurveyResponse->update($columns);
                }

                // Count the number of steps that have been finished
                $countResponses = SurveyResponse::countSurveySurveyorResponses($currentUserId, $surveyId, $assignmentId);
            }
            */

            if(!$complianceSurvey){
                // Count the number of steps that have been finished
                $countResponses = SurveyResponse::countSurveySurveyorResponses($currentUserId, $surveyId, $assignmentId);

                return response()->json([
                    'success' => false,
                    'message' => 'Escolha entre Conforme e Não Conforme',
                    'action' => 'changeToPending',
                    'countResponses' => $countResponses,
                    'countTopics' => $countTopics,
                    'action2' => 'blinkComplianceButtons'
                ]);
            }

            $data['compliance_survey'] = $complianceSurvey;

            if ($id) {
                // Update existing survey response
                $SurveyResponse = SurveyResponse::findOrFail($id);
                $SurveyResponse->update($data);
            } else {
                // Create new survey response
                $SurveyResponse = SurveyResponse::create($data);
            }

            // Count the number of steps that have been finished
            $countResponses = SurveyResponse::countSurveySurveyorResponses($currentUserId, $surveyId, $assignmentId);

            // Return success response
            return response()->json([
                'success' => true,
                'message' => $id ? 'Dados deste tópico foram atualizados' : 'Dados deste tópico foram salvos',
                'id' => $SurveyResponse->id,
                'countResponses' => $countResponses,
                'countTopics' => $countTopics,
                'showFinalizeButton' => $countResponses < $countTopics ? false : true
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error("Illuminate Error in responsesSurveyorStoreOrUpdate: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Registro não encontrado.',
            ]);
        } catch (\Exception $e) {
            \Log::error("Exception Error in responsesSurveyorStoreOrUpdate: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro inesperado.',
            ]);
        }
    }

    public function responsesAuditorStoreOrUpdate(Request $request, $id = null)
    {
        $messages = [
            'id.required' => 'O campo id é obrigatório',
            'survey_id.required' => 'O campo survey_id é obrigatório',
            'step_id.required' => 'O campo step_id é obrigatório',
            'topic_id.required' => 'O campo topic_id é obrigatório',
            //'compliance_audit.required' => 'Marque: Conforme ou Não Conforme',
            //'compliance_audit.in' => 'Marque Apenas se Conforme ou Não Conforme',//'O campo compliance_audit deve ser yes, no ou na.',
        ];

        try {
            $validatedData = Validator::make($request->all(), [
                'id' => 'required',
                'survey_id' => 'required',
                'step_id' => 'required',
                'topic_id' => 'required',
                //'compliance_audit' => 'required|in:yes,no,na',
                //'comment_audit' => 'sometimes|string',
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

        try {
            // Get authenticated user ID
            $currentUserId = auth()->id();

            $assignmentId = $request->input('assignment_id');

            $assignmentData = SurveyAssignments::findOrFail($assignmentId);
            $assignmentCreatedAt = $assignmentData->created_at;

            $now = Carbon::now()->startOfDay();
            $timeLimit = $assignmentCreatedAt->endOfDay();

            $surveyId = $assignmentData->survey_id;
            $surveyData = Survey::findOrFail($surveyId);
            $surveyRecurring = $surveyData->recurring;

            if(in_array($surveyRecurring, ['once', 'daily'])){
                if ($now->gt($timeLimit)) {
                    // Change SurveyorAssignment status
                    SurveyAssignments::changeAuditorAssignmentStatus($assignmentId, 'losted');

                    return response()->json([
                        'success' => false,
                        'message' => 'O prazo encerrou e esta Auditoria foi perdida'
                    ]);
                }
            }

            // Change SurveyorAssignment status
            SurveyAssignments::changeAuditorAssignmentStatus($assignmentId, 'in_progress');

            // Prepare data for saving
            $data = $request->only(['auditor_id', 'step_id', 'topic_id', 'survey_id', 'assignment_id', 'company_id', 'compliance_audit', 'comment_audit', 'attachments_audit']);

            $data['auditor_id'] = $currentUserId;

            $data['assignment_id'] = $assignmentId;

            $countTopics = SurveyTopic::countSurveyTopics($surveyId);

            // Prevent error from JavaScript if input[name="response_id"] was cracked.
            // Check if exists the response. If exist get the Id and update.
            $existingResponse = SurveyResponse::where('survey_id', $surveyId)
                ->where('assignment_id', $assignmentId)
                ->where('step_id', $data['step_id'])
                ->where('topic_id', $data['topic_id'])
                ->first();
            if($existingResponse){
                $id = $existingResponse->id;
            }

            if ($currentUserId != $assignmentData->auditor_id) {
                return response()->json(['success' => false, 'message' => 'Você não possui autorização para prosseguir com a tarefa delegada a outra pessoa']);
            }

            $auditorAssignmentStatus = $assignmentData->auditor_status;
            if($auditorAssignmentStatus == 'completed'){
                return response()->json([
                    'success' => false,
                    'message' => 'Esta Auditoria já foi finalizada e não poderá ser editada',
                ]);
            }
            if($auditorAssignmentStatus == 'losted'){
                return response()->json([
                    'success' => false,
                    'message' => 'Esta Auditoria foi perdida pois o prazo expirou e por isso não poderá mais ser editada',
                ]);
            }

            $complianceAudit = $request->input('compliance_audit');

            $attachmentIds = $request->input('attachment_ids');

            /*
            if( $complianceAudit == 'no' && !$attachmentIds ){
                if ($id) {
                    // Update existing survey response
                    $SurveyResponse = SurveyResponse::findOrFail($id);

                    $columns['attachments_audit'] = null;
                    $columns['compliance_audit'] = null;
                    $SurveyResponse->update($columns);
                }

                // Count the number of steps that have been finished
                $countResponses = SurveyResponse::countSurveyAuditorResponses($currentUserId, $surveyId, $assignmentId);

                return response()->json([
                    'success' => false,
                    'message' => 'Se você Discorda, será necessário enviar ao menos uma foto comprovando o motivo',
                    'action' => 'changeToPending',
                    'action2' => 'blinkPhotoButton',
                    'countResponses' => $countResponses,
                    'countTopics' => $countTopics
                ]);
            }elseif($complianceAudit == 'yes'){
                if ($id) {
                    // Update existing survey response
                    $SurveyResponse = SurveyResponse::findOrFail($id);

                    $columns['compliance_audit'] = $complianceAudit;
                    $SurveyResponse->update($columns);
                }
                // Count the number of steps that have been finished
                $countResponses = SurveyResponse::countSurveyAuditorResponses($currentUserId, $surveyId, $assignmentId);
            }
            */
            $attachmentIdsInt = $attachmentIds ? array_map('intval', $attachmentIds) : [];

            // Check if attachmentIdsInt is empty and handle accordingly
            $data['attachments_audit'] = !empty($attachmentIdsInt) ? json_encode($attachmentIdsInt) : null;

            $comment = $request->input('comment_audit');
            $comment = trim($comment);

            /*
            if( $complianceAudit == 'no' && empty($comment) ){
                if ($id) {
                    // Update existing survey response
                    $SurveyResponse = SurveyResponse::findOrFail($id);

                    $columns['compliance_audit'] = null;
                    $SurveyResponse->update($columns);
                }

                // Count the number of steps that have been finished
                $countResponses = SurveyResponse::countSurveyAuditorResponses($currentUserId, $surveyId, $assignmentId);

                return response()->json([
                    'success' => false,
                    'message' => 'Necessário descrever o motivo de haver Indeferido',
                    'action' => 'changeToPending',
                    'action2' => 'showTextarea',
                    'countResponses' => $countResponses,
                    'countTopics' => $countTopics
                ]);
            }elseif($complianceAudit == 'yes'){
                if ($id) {
                    // Update existing survey response
                    $SurveyResponse = SurveyResponse::findOrFail($id);

                    $columns['compliance_audit'] = $complianceAudit ;
                    $SurveyResponse->update($columns);
                }
                // Count the number of steps that have been finished
                $countResponses = SurveyResponse::countSurveyAuditorResponses($currentUserId, $surveyId, $assignmentId);
            }
            */

            if(!$complianceAudit){
                // Count the number of steps that have been finished
                $countResponses = SurveyResponse::countSurveyAuditorResponses($currentUserId, $surveyId, $assignmentId);

                return response()->json([
                    'success' => false,
                    'message' => 'Escolha entre Concordo e Não Concordo',
                    'action' => 'changeToPending',
                    'countResponses' => $countResponses,
                    'countTopics' => $countTopics,
                    'action2' => 'blinkComplianceButtons'
                ]);
            }

            $data['compliance_audit'] = $complianceAudit;

            if ($id) {
                // Update existing survey response
                $SurveyResponse = SurveyResponse::findOrFail($id);
                $SurveyResponse->update($data);
            } else {
                // Create new survey response
                $SurveyResponse = SurveyResponse::create($data);
            }

            // Count the number of steps that have been finished
            $countResponses = SurveyResponse::countSurveyAuditorResponses($currentUserId, $surveyId, $assignmentId);

            // Return success response
            return response()->json([
                'success' => true,
                'message' => $id ? 'Dados deste tópico foram atualizados' : 'Dados deste tópico foram salvos',
                'id' => $SurveyResponse->id,
                'countResponses' => $countResponses,
                'countTopics' => $countTopics,
                'showFinalizeButton' => $countResponses < $countTopics ? false : true
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error("Illuminate Error in responsesAuditorStoreOrUpdate: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Registro não encontrado.',
            ]);
        } catch (\Exception $e) {
            \Log::error("Exception Error in responsesAuditorStoreOrUpdate: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro inesperado.',
            ]);
        }
    }

}
