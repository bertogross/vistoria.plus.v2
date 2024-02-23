<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SurveyResponse extends Model
{
    use HasFactory;

    protected $connection = 'vpAppTemplate';

    public $timestamps = true;

    protected $fillable = [
        //'id',
        'surveyor_id',
        'auditor_id',
        'step_id',
        'topic_id',
        'survey_id',
        'assignment_id',
        'compliance_survey',
        'compliance_audit',
        'comment_survey',
        'comment_audit',
        'attachments_survey',
        'attachments_audit'
    ];

    // Count the number of responses from surveyor
    public static function countSurveySurveyorResponses($surveyorId, $surveyId, $assignmentId) {
        //$today = Carbon::today();

        return SurveyResponse::where('survey_id', $surveyId)
            ->where('surveyor_id', $surveyorId)
            ->where('assignment_id', $assignmentId)
            ->whereNotNull('compliance_survey')
            //->whereNotNull('attachments_survey')
            //->whereDate('created_at', '=', $today)
            ->count();
    }

    // Count the number of responses from surveyor
    public static function countSurveySurveyorResponsesByCompliance($surveyorId, $surveyId, $assignmentId, $choice) {
        //$today = Carbon::today();

        return SurveyResponse::where('survey_id', $surveyId)
            ->where('surveyor_id', $surveyorId)
            ->where('assignment_id', $assignmentId)
            //->whereNotNull('compliance_survey')
            ->where('compliance_survey', $choice)
            ->count();
    }

    // Count the number of responses from auditor
    public static function countSurveyAuditorResponses($auditorId, $surveyId, $assignmentId) {
        //$today = Carbon::today();

        return SurveyResponse::where('survey_id', $surveyId)
            ->where('auditor_id', $auditorId)
            ->where('assignment_id', $assignmentId)
            ->whereNotNull('compliance_audit')
            //->whereNotNull('attachments_audit')
            //->whereDate('created_at', '=', $today)
            ->count();
    }

}
