<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Spatie\Dropbox\Client as DropboxClient;
use App\Http\Controllers\SettingsAccountController;


//https://dropbox.github.io/dropbox-api-v2-explorer/#users_get_space_usage
//https://www.dropbox.com/developers/documentation/http/documentation#users-get_space_usage

class DropboxController extends Controller
{
    protected $settingsAccountController;

    /**
     * Constructor to initialize the SettingsAccountController.
     */
    public function __construct(SettingsAccountController $settingsAccountController)
    {
        $this->settingsAccountController = $settingsAccountController;
    }

    /**
     * Display files and folders in the root directory of Dropbox.
     */
    public function index(Request $request)
    {
        $dropboxToken = getDropboxToken('dropbox_token');

        if (!$dropboxToken) {
            //return redirect(route('settingsApiKeysURL'))->with('error', 'Conecte-se ao Dropbox primeiro.');
            return view('settings.dropbox')->with('error', 'Conecte-se ao Dropbox primeiro.');
        }

        $dropbox = new DropboxClient($dropboxToken);

        try {
            $response = $dropbox->listFolder('/');
        } catch (\Exception $e) {
            return redirect(route('settingsApiKeysURL'))->with('error', 'Falha ao atualizar o token de acesso. Clique em Conectar ao Dropbox');

            /*
            if ($e->getResponse()->getStatusCode() == 401) {
                // Handle the 401 Unauthorized error
                if ($refreshToken) {
                    // Use the refresh token to obtain a new access token
                    $newAccessToken = $this->getNewAccessToken($refreshToken);
                    if ($newAccessToken) {
                        // Update the access token in the database
                        updateDropboxToken('dropbox_token', $newAccessToken);

                        // Retry the API request with the new access token
                        $dropbox = new DropboxClient($newAccessToken);
                        try {
                            $response = $dropbox->listFolder('/');
                        } catch (ClientException $e) {
                            return redirect(route('settingsApiKeysURL'))->with('error', 'Falha ao recuperar arquivos e pastas do Dropbox.');
                        }
                    } else {
                        return redirect(route('settingsApiKeysURL'))->with('error', 'Falha ao atualizar o token de acesso.');
                    }
                } else {
                    return redirect(route('settingsApiKeysURL'))->with('error', 'Sua conexão com o Dropbox expirou. Reconecte-se.');
                }
            } else {
                // Handle other 4xx errors
                return redirect(route('settingsApiKeysURL'))->with('error', 'Falha ao recuperar arquivos e pastas do Dropbox.');
            }
            */
        }

        if (!isset($response['entries'])) {
            return redirect(route('settingsApiKeysURL'))->with('error', 'Falha ao recuperar arquivos e pastas do Dropbox.');
        }

        $items = $response['entries'];

        // Get the current page and per-page limit from the URL parameters
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        $offset = ($page - 1) * $perPage; // calculate offset

        $files = [];
        $folders = [];

        foreach ($items as $item) {
            if ($item['.tag'] === 'file') {
                $item['link'] = $dropbox->getTemporaryLink($item['path_display']);
                $files[] = $item;
            } elseif ($item['.tag'] === 'folder') {
                $folderContents = $dropbox->listFolder($item['path_display']);
                $folderSize = 0;
                $fileCount = 0;

                while (true) {
                    foreach ($folderContents['entries'] as $folderItem) {
                        if ($folderItem['.tag'] === 'file') {
                            $folderSize += $folderItem['size'];
                            $fileCount++;
                        }
                    }

                    if (!$folderContents['has_more']) {
                        break;
                    }

                    $folderContents = $dropbox->listFolderContinue($folderContents['cursor']);
                }
                $folderSizeGB = $folderSize / (1024 ** 3);
                $item['size'] = number_format($folderSizeGB, 2);

                $item['file_count'] = $fileCount;
                $folders[] = $item;
            }
        }


        // Sort files by date in descending order
        usort($files, function ($a, $b) {
            return strtotime($b['client_modified']) <=> strtotime($a['client_modified']);
        });

        // Get the file type from the URL parameter
        $fileType = $request->query('fileType', 'All');

        // Filter the files based on the file type
        if ($fileType !== 'All') {
            $files = $this->filterFilesByType($files, $fileType);
        }

        // Get the total number of files
        $totalFiles = count($files);

        // Get the subset of files for the current page
        $files = array_slice($files, ($page - 1) * $perPage, $perPage);

        $storageInfo = $this->getStorageInfo($dropboxToken);

        $currentFolderPath = $request->input('path', '');

        return view('settings.dropbox', compact('files', 'folders', 'storageInfo', 'fileType', 'page', 'perPage', 'totalFiles', 'currentFolderPath'));
    }

    /**
     * Filter files by type.
     */
    private function filterFilesByType($files, $fileType)
    {
        return array_filter($files, function ($file) use ($fileType) {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            return $this->isFileType($extension, $fileType);
        });
    }

    /**
     * Check if the file extension matches the file type.
     */
    private function isFileType($extension, $fileType)
    {
        $types = [
            'Images' => ['jpg', 'jpeg', 'png', 'gif'],
            'Documents' => ['pdf', 'txt', 'doc', 'docx'],
            'Music' => ['mp3', 'wav'],
            'Video' => ['mp4', 'avi', 'mov'],
        ];

        return in_array($extension, $types[$fileType] ?? []);
    }


    /**
     * Browse a specific folder in Dropbox.
     */
    public function browseFolder($path, Request $request)
    {
        $dropboxToken = getDropboxToken('dropbox_token');

        if (!$dropboxToken) {
            return redirect(route('DropboxIndexURL'))->with('error', 'Conecte-se ao Dropbox primeiro.');
        }

        $dropbox = new DropboxClient($dropboxToken);
        $response = $dropbox->listFolder('/' . $path);

        if (!isset($response['entries'])) {
            return redirect(route('DropboxIndexURL'))->with('error', 'Falha ao recuperar arquivos e pastas do Dropbox.');
        }

        $items = $response['entries'];

        // Get the current page and per-page limit from the URL parameters
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        $offset = ($page - 1) * $perPage; // calculate offset

        $files = [];
        $folders = [];

        foreach ($items as $item) {
            if ($item['.tag'] === 'file') {
                $item['link'] = $dropbox->getTemporaryLink($item['path_display']);
                $files[] = $item;
            } elseif ($item['.tag'] === 'folder') {
                $folders[] = $item;
            }
        }

        // Sort files by date in descending order
        usort($files, function ($a, $b) {
            return strtotime($b['client_modified']) <=> strtotime($a['client_modified']);
        });

        // Get the total number of files
        $totalFiles = count($files);

        // Get the subset of files for the current page
        $files = array_slice($files, ($page - 1) * $perPage, $perPage);

        $storageInfo = $this->getStorageInfo($dropboxToken);

        $currentFolderPath = $path;

        return view('settings.dropbox', compact('files', 'folders', 'storageInfo', 'page', 'perPage', 'totalFiles', 'path', 'currentFolderPath'));
    }

    /**
     * Get a new access token using the refresh token.
     */
    private function getNewAccessToken($refreshToken)
    {
        $appKey = env('DROPBOX_APP_KEY');
        $appSecret = env('DROPBOX_APP_SECRET');

        $client = new Client(['verify' => false]);
        $response = $client->post('https://api.dropboxapi.com/oauth2/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => $appKey,
                'client_secret' => $appSecret,
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        return $data['access_token'] ?? null;
    }

    /**
     * Get storage information from Dropbox.
     */
    private function getStorageInfo($dropboxToken)
    {
        $spaceInfo = $this->getSpaceUsage($dropboxToken);
        //dd($spaceInfo);

        if (!is_array($spaceInfo) || !isset($spaceInfo['used'])) {
            throw new \Exception('Falha ao recuperar informações de armazenamento do Dropbox.');
        }

        // Convert to GB
        $used = $spaceInfo['used'] ? $spaceInfo['used'] / (1024 * 1024 * 1024) : 0;
        // Convert to GB
        $total = $spaceInfo['allocation'] ? $spaceInfo['allocation']['allocated'] / (1024 * 1024 * 1024) : 0;

        $percentageUsed = $used > 0 && $total > 0 ? ($used / $total) * 100 : 0;

        $used = floatval($spaceInfo['used']);
        return [
            'total' => number_format($total, 2),
            'used' => $used,
            'percentageUsed' => number_format($percentageUsed, 2),
        ];
    }


    /**
     * Get space usage information from Dropbox.
     */
    public function getSpaceUsage($dropboxToken)
    {
        $client = new Client(['verify' => false]);
        $response = $client->request('POST', 'https://api.dropboxapi.com/2/users/get_space_usage', [
            'headers' => [
                'Authorization' => 'Bearer ' . $dropboxToken,
                //'Content-Type' => 'application/json',
            ],
        ]);

        $data = $response ? json_decode($response->getBody(), true) : '';

        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::error('JSON decode error: ' . json_last_error_msg());
            return null;
        }

        \Log::info('Dropbox API response: ' . print_r($data, true));
        return $data;
    }

    /**
     * Delete a file from Dropbox.
     */
    public function deleteFile(Request $request)
    {
        $dropboxToken = getDropboxToken('dropbox_token');

        if (!$dropboxToken) {
            return response()->json(['success' => false, 'message' => 'Conecte-se ao Dropbox primeiro.']);
        }

        $dropbox = new DropboxClient($dropboxToken);

        $path = $request->input('path');

        try {
            $dropbox->delete($path);
            return response()->json(['success' => true, 'message' => 'Arquivo excluído com êxito', 'path' => $path]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Falha ao excluir o arquivo']);
        }
    }

    /**
     * Delete a folder and its contents from Dropbox.
     */
    public function deleteFolder($path)
    {
        $dropboxToken = getDropboxToken('dropbox_token');

        if (!$dropboxToken) {
            return redirect(route('DropboxIndexURL'))->with('error', 'Conecte-se ao Dropbox primeiro.');
        }

        $dropbox = new DropboxClient($dropboxToken);

        try {
            // List all files and folders in the folder
            $response = $dropbox->listFolder('/' . $path);

            if (isset($response['entries'])) {
                $items = $response['entries'];

                // Delete each file and folder
                foreach ($items as $item) {
                    if ($item['.tag'] === 'file') {
                        $dropbox->delete($item['path_display']);
                    } elseif ($item['.tag'] === 'folder') {
                        $this->deleteFolder(ltrim($item['path_display'], '/'));
                    }
                }
            }

            // Delete the folder itself
            $dropbox->delete('/' . $path);

            return redirect(route('DropboxIndexURL'))->with('success', 'Pasta excluída com êxito');
        } catch (\Exception $e) {
            return redirect(route('DropboxIndexURL'))->with('error', 'Falha ao excluir pasta: ' . $e->getMessage());
        }
    }

    /**
     * Upload a file to Dropbox.
     */
    public function uploadFile(Request $request)
    {
        $dropboxToken = getDropboxToken('dropbox_token');

        if (!$dropboxToken) {
            return response()->json(['error' => 'Conecte-se ao Dropbox primeiro.'], 401);
        }

        $dropbox = new DropboxClient($dropboxToken);

        $path = $request->header('Dropbox-API-Arg');
        $path = json_decode($path, true)['path'];
        $contents = file_get_contents('php://input');

        try {
            $response = $dropbox->upload($path, $contents, 'add', true);

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Falha ao carregar arquivo: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle the callback from Dropbox after authorization.
     */
    public function callback(Request $request)
    {
        $appKey = env('DROPBOX_APP_KEY');
        $appSecret = env('DROPBOX_APP_SECRET');
        $redirectUri = route('DropboxCallbackURL');

        if (!$request->has('code')) {
            return redirect(route('settingsApiKeysURL'))->with('error', 'Falha na autorização. Tente novamente.');
        }

        $client = new Client(['verify' => false]);
        $response = $client->post('https://api.dropboxapi.com/oauth2/token', [
            'form_params' => [
                'code' => $request->input('code'),
                'grant_type' => 'authorization_code',
                'client_id' => $appKey,
                'client_secret' => $appSecret,
                'redirect_uri' => $redirectUri,
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        if (!isset($data['access_token'])) {
            return redirect(route('settingsApiKeysURL'))->with('error', 'Falha na autorização. Tente novamente.');
        }

        $this->settingsAccountController->updateOrInsertSetting('dropbox_token', $data['access_token']);

        return redirect(route('settingsApiKeysURL'))->with('success', 'O Dropbox foi conectado.');
    }

    /**
     * Redirect to Dropbox for authorization.
     */
    public function authorizeDropbox()
    {
        $appKey = env('DROPBOX_APP_KEY');
        $redirectUri = route('DropboxCallbackURL');

        $authUrl = "https://www.dropbox.com/oauth2/authorize?client_id={$appKey}&response_type=code&redirect_uri={$redirectUri}";

        return redirect($authUrl);
    }

    /**
     * Deauthorize Dropbox and remove the access token from the database.
     */
    public function deauthorizeDropbox()
    {
        $this->settingsAccountController->updateOrInsertSetting('dropbox_token', null);

        return redirect(route('settingsApiKeysURL'))->with('success', 'O Dropbox foi desconectado.');
    }
}
