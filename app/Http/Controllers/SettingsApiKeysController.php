<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class SettingsApiKeysController extends Controller
{
    /**
     * Display the API keys settings page.
     */
    public function index()
    {
        $dropboxToken = getDropboxToken('dropbox_token');

        $DropBoxUserAccountInfo = $this->DropBoxUserAccountInfo($dropboxToken);

        return view('settings.api-keys', compact('DropBoxUserAccountInfo'));
    }


    /**
     * Retrieve the current user's account information from Dropbox.
     */
    private function DropBoxUserAccountInfo($dropboxToken)
    {
        $client = new Client(['verify' => false]);
        try {
            $response = $client->request('POST', 'https://api.dropboxapi.com/2/users/get_current_account', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $dropboxToken,
                ],
            ]);

            $data = $response ? json_decode($response->getBody(), true) : '';

            if ($data) {
                if (json_last_error() !== JSON_ERROR_NONE) {
                    \Log::error('JSON decode error: ' . json_last_error_msg());
                    return null;
                }

                \Log::info('Dropbox API response: ' . print_r($data, true));
                return $data;
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            \Log::error('GuzzleHttp client error: ' . $e->getMessage());
            return null;
        }
    }

}
