<?php

namespace App\Http\Controllers;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use GuzzleHttp\Client;
use App\Http\Controllers\SettingsAccountController;

class GoogleDriveController extends Controller
{
    protected $connection = 'vpAppTemplate';

    public $timestamps = true;

    private $drive;

    private $settingsAccountController;

    public function __construct(SettingsAccountController $settingsAccountController)
    {
        $this->drive = new Google_Service_Drive($this->getClient());
        $this->settingsAccountController = $settingsAccountController;
    }


    public function index()
    {
        return view('GoogleDriveFilesURL');
    }

    public function files()
    {
        $client = $this->getClient();
        $service = new Google_Service_Drive($client);
        $files = $service->files->listFiles();
        $folders = [];
        $storageInfo = $this->getStorageInfo();
        return view('GoogleDriveFilesURL', compact('files', 'folders', 'storageInfo'));
    }

    private function getStorageInfo()
    {
        $client = $this->getClient();
        $service = new Google_Service_Drive($client);
        $about = $service->about->get(['fields' => 'storageQuota']);
        $storageQuota = $about->getStorageQuota();
        $total = $storageQuota->getLimit() / (1024 * 1024 * 1024); // Convert to GB
        $used = $storageQuota->getUsage() / (1024 * 1024 * 1024); // Convert to GB
        $percentageUsed = ($used / $total) * 100;
        return [
            'total' => number_format($total, 2),
            'used' => number_format($used, 2),
            'percentageUsed' => number_format($percentageUsed, 2),
        ];
    }


    public function redirect()
    {
        $client = $this->getClient();
        $authUrl = $client->createAuthUrl();
        return redirect($authUrl);
    }

    public function callback(Request $request)
    {
        $client = $this->getClient();

        try {
            $token = $client->fetchAccessTokenWithAuthCode($request->code);
            $client->setAccessToken($token);
            Log::info('Google token: ' . json_encode($token));
            session(['google_token' => $token]);

            // Store the token in the database
            $this->settingsAccountController->updateOrInsertSetting('google_token', json_encode($token));

            return redirect()->route('GoogleDriveFilesURL');
        } catch (Exception $e) {
            Log::error('Google API error: ' . $e->getMessage());
            return redirect()->route('settingsApiKeysURL')->with('error', 'Failed to authenticate with Google. Please try again.');
        }
    }



    public function upload(Request $request)
    {
        $client = $this->getClient();
        $service = new Google_Service_Drive($client);

        $file = new Google_Service_Drive_DriveFile();
        $file->setName($request->file('file')->getClientOriginalName());

        $result = $service->files->create(
            $file,
            [
                'data' => file_get_contents($request->file('file')->getRealPath()),
                'mimeType' => $request->file('file')->getMimeType(),
                'uploadType' => 'multipart',
            ]
        );

        return redirect()->route('GoogleDriveFilesURL');
    }

    public function delete($fileId)
    {
        $client = $this->getClient();
        $service = new Google_Service_Drive($client);
        $service->files->delete($fileId);
        return redirect()->route('GoogleDriveFilesURL');
    }

    private function getClient()
    {
        $client = new Google_Client();
        $client->setClientId(config('google.client_id'));
        $client->setClientSecret(config('google.client_secret'));
        $client->setRedirectUri(config('google.redirect_uri'));
        $client->setScopes(config('google.scopes'));
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Disable SSL verification
        $client->setHttpClient(new Client(['verify' => false]));

        $token = session('google_token');
        if ($token) {
            $client->setAccessToken($token);
        }

        return $client;
    }

    public function deauthorize()
    {
        // Remove the Google token from the session
        session()->forget('google_token');

        // Remove the Google token from the database
        $this->settingsAccountController->updateOrInsertSetting('google_token', null);

        // Redirect the user with a success message
        return redirect()->route('settingsApiKeysURL')->with('success', 'Google Drive has been deauthorized successfully.');
    }

}
