<?php

namespace App\Models;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    //Get attachment ID by path
    public static function getAttachmentIdByPath($path)
    {
        return DB::connection('vpAppTemplate')->table('attachments')->where('path', $path)->value('id');
    }

    // Delete an attachment ID from a JSON column in the survey_responses table.
    public static function deleteAttachmentIdFromJsonColumn($columnName, $attachmentId)
    {
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
