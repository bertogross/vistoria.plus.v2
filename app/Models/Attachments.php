<?php

namespace App\Models;

use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attachments extends Model
{
    use HasFactory;

    protected $connection = 'vpAppTemplate';

    public $timestamps = true;

    protected $fillable = ['user_id', 'parent_id', 'path', 'type', 'title', 'description', 'size', 'order'];

    public static function getAttachmentPathById($attachmentId)
    {
        if($attachmentId){
            $attachment = self::find($attachmentId);
            return $attachment ? URL::asset('storage/'.$attachment->path) : '';
            //URL::asset('storage/' .. )

        }
    }


    public function deletePhoto(Request $request = null, $attachmentId)
    {
        if($attachmentId){
            try {
                // Retrieve the attachment from the database
                 $attachment = Attachments::find($attachmentId);

                if (!$attachment) {
                    return response()->json(['success' => false, 'message' => 'Anexo não encontrado'], 404);
                }

                // Delete the file from storage
                if (Storage::disk('public')->exists($attachment->path)) {
                    Storage::disk('public')->delete($attachment->path);
                }

                // Delete the attachment record from the database
                $attachment->delete();

                // Delete the attachment id from the survey_responses table collum attachments_survey/attachments_audit
                Attachments::deleteAttachmentIdFromJsonColumn('attachments_survey', $attachmentId);
                Attachments::deleteAttachmentIdFromJsonColumn('attachments_audit', $attachmentId);

                return response()->json(['success' => true, 'message' => 'Anexo excluído com êxito'], 200);

            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        }
    }


    // Delete an attachment ID from a JSON column in the survey_responses table.
    public static function deleteAttachmentIdFromJsonColumn($columnName, $attachmentId) {
        DB::connection('vpAppTemplate')->table('survey_responses')
            ->whereNotNull($columnName) // Check if the column is not null
            ->whereJsonContains($columnName, $attachmentId)
            ->update([
                $columnName => DB::raw("JSON_REMOVE(
                    `$columnName`,
                    REPLACE(JSON_SEARCH(`$columnName`, 'one', '{$attachmentId}'), '\"', '')
                )")
            ]);
    }


    public static function getAttachmentDateById($attachmentId)
    {
        if($attachmentId){
            $attachment = self::find($attachmentId);
            return $attachment ? date("d/m/Y H:i", strtotime($attachment->created_at)) : '';
            //URL::asset('storage/' .. )

        }
    }


}
