<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserUploadController extends Controller
{
    // Define the custom database connection name
    protected $connection = 'vpOnboard';

    // Fill created_at
    public $timestamps = true;

    // Handle avatar upload
    public function uploadAvatar(Request $request)
    {
        // Delegate the upload process to the generic uploadFile method
        return $this->uploadFile($request, 'avatar', 'avatars');
    }

    // Handle cover image upload
    public function uploadCover(Request $request)
    {
        // Delegate the upload process to the generic uploadFile method
        return $this->uploadFile($request, 'cover', 'covers');
    }

    // Generic method to handle file uploads.
    private function uploadFile(Request $request, $type, $folder)
    {
        $freeDiskSpace = checkFreeDiskSpace();
        if($freeDiskSpace <= 0){
            return response()->json(['success' => false, 'message' => 'Espaço em disco insuficiente'], 404);

        }
        try {
            // Validate the incoming request data
            $messages = [
                'file.mimes' => 'Envie somente extensão JPG',
                'file.max' => 'O arquivo deve pesar no máximo 5MB',
            ];

            $request->validate([
                'file' => 'required|file|mimes:jpeg,jpg|max:5120',
                //'user_id' => 'required|integer|exists:vpAppTemplate.users,id'
            ], $messages);

            $userID = $request->input('user_id');
            $userID = $userID ? intval($userID) : auth()->id();
            $user = User::on($this->connection)->find($userID);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $config = config("database.connections.{$this->connection}");
                $dbName = $config['database'];
                //$path = $folder;
                $path = $folder . '/' . date('Y') . '/' . date('m');

                // Ensure the directory exists
                if (!Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->makeDirectory($path);
                }

                $filePath = $file->store($path, 'public');

                if ($type === 'avatar') {
                    if ($user->avatar) {
                        Storage::disk('public')->delete($user->avatar);
                    }
                    $user->avatar = $filePath;
                } elseif ($type === 'cover') {
                    if ($user->cover) {
                        Storage::disk('public')->delete($user->cover);
                    }
                    $user->cover = $filePath;
                }

                if ($user->isDirty()) {
                    $user->save();

                    return response()->json(['success' => true, 'message' => ucfirst($type) . ' carregado com sucesso', 'path' => $filePath, 'userId' => $user->id], 200);
                } else {
                    Log::info('No changes detected for user: ' . $user->id);

                    Storage::disk('public')->delete($filePath);

                    return response()->json(['success' => false, 'message' => 'No changes detected or save operation failed'], 422);
                }
            }

            return response()->json(['success' => false, 'message' => 'Arquivo não fornecido'], 422);
        } catch (\Exception $e) {
            Log::error("File upload error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function uploadLogo(Request $request)
    {
        try {
            $messages = [
                'file.required' => 'Envie um arquivo de imagem',
                'file.file' => 'O arquivo deve ser uma imagem',
                'file.mimes' => 'Envie somente extensão JPG ou PNG',
                'file.max' => 'O arquivo não deve pesar mais de 5MB',
            ];

            // Validate the uploaded file
            $validatedData = $request->validate([
                'file' => 'required|file|mimes:jpeg,jpg,png|max:5120', // Only allow JPEG images up to 5MB
            ], $messages);

            if ($request->hasFile('file')) {
                // Store the uploaded file
                $file = $request->file('file');

                // Check if there's an old logo and delete it
                $oldLogo = DB::connection('vpAppTemplate')->table('settings')->where('key', 'logo')->value('value');
                if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                }

                // Get the database name from the connection configuration
                $config = config("database.connections.{$this->connection}");
                $dbName = $config['database'];

                $folder = 'logo';

                $path = "{$dbName}/" . $folder . '/' . date('Y') . '/' . date('m');

                // Ensure the directory exists
                if (!Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->makeDirectory($path);
                }
                /*if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }*/

                $filePath = $file->store($path, 'public');

                // Save the file path in the settings table
                $settings = DB::connection('vpAppTemplate')->table('settings')->updateOrInsert(
                    ['key' => 'logo'],
                    ['value' => $filePath]
                );

                return response()->json(['success' => true, 'message' => 'Logo enviado com sucesso!', 'path' => $filePath], 200);
            }

            return response()->json(['success' => false, 'message' => 'Arquivo não fornecido'], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteLogo(Request $request)
    {
        try {
            // Retrieve the logo path from the settings table
            $logoPath = DB::connection('vpAppTemplate')->table('settings')->where('key', 'logo')->value('value');

            // Check if the logo exists and delete it
            if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->delete($logoPath);

                // Optionally, you can also remove the logo path from the settings table
                DB::connection('vpAppTemplate')->table('settings')->where('key', 'logo')->delete();

                return response()->json(['success' => true, 'message' => 'Logo removido!'], 200);
            }

            return response()->json(['success' => false, 'message' => 'Logo não encontrado'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


}
