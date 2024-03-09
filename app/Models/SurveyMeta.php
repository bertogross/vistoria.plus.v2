<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * SurveyMeta Model
 *
 * Represents the metadata associated with a user.
 */
class SurveyMeta extends Model
{
    use HasFactory;

    // Specifies the database connection for this model.
    protected $connection = 'vpAppTemplate';

    // The attributes that are mass assignable.
    protected $fillable = [
        'survey_id', 'meta_key', 'meta_value'
    ];

    /**
     * Retrieve a survey's meta value based on the given key.
     *
     * @param int    $surveyId The ID of the survey.
     * @param string $metaKey The meta key to retrieve.
     * @return string|null The meta value or null if not found.
     */
    public static function getSurveyMeta($surveyId, $metaKey)
    {
        // Query the 'survey_metas' table to find the meta value for the given survey and key.
        $meta = DB::connection('vpAppTemplate')
                  ->table('survey_metas')
                  ->where('survey_id', $surveyId)
                  ->where('meta_key', $metaKey)
                  ->first();

        // Return the meta value if found, otherwise return null.
        return $meta ? $meta->meta_value : null;
    }

    /**
     * Update or create a survey's meta value based on the given key.
     *
     * @param int    $surveyId The ID of the survey.
     * @param string $metaKey    The meta key to update or create.
     * @param string $metaValue  The meta value to set.
     * @return void
     */
    public static function updateSurveyMeta($surveyId, $metaKey, $metaValue)
    {
        if($metaValue !== null){
            // Update or create the 'survey_metas' record for the given survey and key.
            return DB::connection('vpAppTemplate')
                ->table('survey_metas')
                ->updateOrInsert(
                    ['survey_id' => $surveyId, 'meta_key' => $metaKey],
                    ['meta_value' => $metaValue]
                );
        }else{
            /// Delete the 'survey_metas' record for the given survey and key.
            return DB::connection('vpAppTemplate')
                ->table('survey_metas')
                ->where('survey_id', $surveyId)
                ->where('meta_key', $metaKey)
                ->delete();

        }
    }

}
