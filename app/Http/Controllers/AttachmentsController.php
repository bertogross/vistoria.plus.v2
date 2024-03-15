<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Attachments;

class AttachmentsController extends Controller
{
    protected $connection = 'vpAppTemplate';

    public function uploadPhoto(Request $request)
    {
        $currentUserId = auth()->id();

        $connectionId = getCurrentConnectionByUserId($currentUserId);

        $subscriptionData = getSubscriptionData($connectionId);
        $subscriptionStatus = $subscriptionData['subscription_status'] ?? null;
        if($subscriptionStatus != 'active'){
            return response()->json(['success' => false, 'action' => 'subscriptionAlert', 'message' => "Para prosseguir você deverá primeiramente ativar sua assinatura"], 200);
        }


        $folder = 'attachments';

        try {
            // Validate the incoming request data
            $messages = [
                'file.mimes' => 'Envie somente extensão JPG',
                'file.max' => 'O arquivo deve pesar no máximo 5MB',
            ];
            $request->validate([
                'file' => 'required|file|mimes:jpeg,jpg|max:5120',
            ], $messages);

            $currentUserId = auth()->id();

            // Check if a file was provided in the request
            if ($request->hasFile('file')) {
                $file = $request->file('file');

                $fileExtension = $file->getClientOriginalExtension();

                // Get the database name from the connection configuration
                $config = config("database.connections.{$this->connection}");
                $dbName = $config['database'];

                $path = "{$dbName}/" . $folder . '/' . date('Y') . '/' . date('m');

                // Ensure the directory exists
                if (!Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->makeDirectory($path);
                }
                /*if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }*/

                $filePath = $file->store($path, 'public');

                if(!$filePath){
                    return response()->json(['success' => false, 'message' => 'Falha ao armazenar o arquivo'], 422);
                }

                // Inside uploadFile method
                $attachment = new Attachments();
                $attachment->user_id = $currentUserId;
                $attachment->path = $filePath;
                $attachment->type = $fileExtension;
                // $attachment->title = '...';
                // $attachment->description = '...';
                // $attachment->size = $file->getSize();
                // $attachment->order = ...;

                // Save the updated user data to the database
                $attachment->save();

                return response()->json(['success' => true, 'message' => 'Arquivo armazenado', 'path' => $filePath, 'id' => $attachment->id], 200);
            }

            return response()->json(['success' => false, 'message' => 'Arquivo não fornecido'], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deletePhoto(Request $request = null, $attachmentId)
    {
        if($attachmentId){
            try {
                // Retrieve the attachment from the database
                 $attachment = Attachments::find($attachmentId);

                if (!$attachment) {
                    return response()->json(['success' => false, 'message' => 'Arquivo não encontrado'], 404);
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

                return response()->json(['success' => true, 'message' => 'Arquivo excluído com êxito'], 200);

            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        }
    }


    public function deleteAttachmentByPath(Request $request)
    {
        $path = $request->input('path');

        if ($path) {
            try {
                // Check if the file exists before attempting to delete it
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                    return response()->json(['success' => true, 'message' => 'Arquivo excluído com êxito'], 200);
                } else {
                    // File does not exist
                    return response()->json(['success' => false, 'message' => 'Arquivo não encontrado'], 404);
                }
            } catch (\Exception $e) {
                // Handle any exceptions during the delete operation
                return response()->json(['success' => false, 'message' => 'Erro ao tentar remover arquivo: ' . $e->getMessage()], 500);
            }
        } else {
            // Path parameter is missing
            return response()->json(['success' => false, 'message' => 'Caminho do arquivo é necessário'], 400);
        }
    }



}
