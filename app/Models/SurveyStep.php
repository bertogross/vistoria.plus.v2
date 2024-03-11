<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\SurveyStep;
use App\Models\SurveyTopic;
use App\Models\SurveyTemplates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SurveyStep extends Model
{
    use HasFactory;

    protected $connection = 'vpAppTemplate';

    public $timestamps = true;

    protected $fillable = [
        //'user_id',
        'survey_id',
        'term_id',
        'step_order'
    ];

    /*public function survey()
    {
        return $this->belongsTo(Survey::class);
    }*/


    public function topics()
    {
        return $this->hasMany(SurveyTopic::class, 'step_id');
    }

    public static function populateSurveySteps($templateId, $surveyId)
    {
        $currentUserId = auth()->id();

        $today = Carbon::now()->startOfDay();

        $survey = Survey::findOrFail($surveyId);

        // Prevent assigment if today date is > than end_in and status is stopped
        $currentStatus = $survey->status;
        $endIn = $survey->end_in;
        if($currentStatus == 'stopped' && $today > $endIn){
            $columns['status'] = 'completed';

            $survey->update($columns);

            return;
        }

        try {
            // Delete existing survey steps for the given surveyId
            $surveyStepsExist = SurveyStep::where('survey_id', $surveyId)->exists();
            if($surveyStepsExist){
                SurveyStep::where('survey_id', $surveyId)->delete();
            }

            // Delete existing survey topics for the given surveyId
            $surveyTopicsExist = SurveyTopic::where('survey_id', $surveyId)->exists();
            if($surveyTopicsExist){
                SurveyTopic::where('survey_id', $surveyId)->delete();
            }

            $data = SurveyTemplates::findOrFail($templateId);

            $reorderingData = SurveyTemplates::reorderingData($data);
            $result = $reorderingData ?? null;
            if(!$result){
                \Log::error('populateSurveySteps: result is empty');

                return;
            }

            foreach($result as $stepIndex => $step){
                $stepData = $step['stepData'] ?? null;
                //$termName = $stepData['term_name'] ?? '';
                $termId = $stepData['term_id'] ?? '';
                //$type = $stepData['type'] ?? 'custom';
                $originalPosition = $stepData['original_position'] ?? $stepIndex;
                $newPosition = $stepData['new_position'] ?? $originalPosition;

                $topics = $step['topics'] ?? null;

                $fill = [
                    //'user_id' => $currentUserId,
                    'survey_id' => intval($surveyId),
                    'term_id' => intval($termId),
                    'step_order' => intval($newPosition),
                ];

                $SurveyStep = SurveyStep::create($fill);

                $stepId = $SurveyStep->id;

                if ($stepId) {
                    SurveyTopic::populateSurveyTopics($topics, $stepId, $surveyId);
                } else {
                    SurveyTopic::where('survey_id', $surveyId)->delete();
                }
            }
        } catch (\Exception $e) {
            \Log::error('populateSurveySteps: ' . $e->getMessage());
        }

    }



}
