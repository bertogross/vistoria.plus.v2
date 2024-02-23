<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SurveyTerms extends Model
{
    use HasFactory;

    // Specifies the database connection for this model.
    protected $connection = 'vpAppTemplate';

    public $timestamps = false;

    protected $table = 'survey_terms';

    protected $fillable = ['name', 'slug', 'status']; //'user_id',

    public static function preListing($termsToArray = false)
    {
        $terms = DB::connection('vpAppTemplate')
            ->table('survey_terms')
            ->where('status', 1)
            ->limit(3)
            ->get(['id AS term_id', 'name AS term_name']);

        if($termsToArray){
            // If needed to transform the results into an associative array with 'id' as keys and 'name' as values:
            $termsArray = $terms->mapWithKeys(function ($item) {
                return [$item->term_id => $item->term_name];
            })->toArray();

            return $termsArray ? $termsArray : null;

        }else{
            return $terms ? json_decode($terms, true) : null;
        }
    }

    public static function cleanedName($input) {
        // Trim the input to remove spaces from the beginning and end
        $trimmedInput = trim($input);

        // Replace multiple consecutive spaces with a single space
        $cleanedInput = preg_replace('/\s+/', ' ', $trimmedInput);

        return $cleanedInput;
    }

    public static function getTermNameById($termId){
        if($termId){
            $termId = intval($termId);

            $termName = DB::connection('vpAppTemplate')
                ->table('survey_terms')
                ->where('id', $termId)
                ->value('name');

            return $termName ?? null;
        }
        return null;
    }


}
