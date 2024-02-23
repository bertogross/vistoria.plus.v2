<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

/**
 * DOCs
 * https://github.com/mailersend/mailersend-laravel-driver
 *
 * https://app.mailersend.com/templates/k68zxl22vpklj905/edit
 *
 * https://developers.mailersend.com
 *
 * https://developers.mailersend.com/general.html?_gl=1*1ddc674*_gcl_au*MjAyMTIwOTk3Mi4xNzA1NjA2Njc2
 *
 * https://github.com/mailersend/mailersend-php
 *
 * https://developers.mailersend.com/api/v1/email.html#send-an-email
 * 
 * https://www.mailersend.com/help/rest-api-response-codes
 * 403 Forbidden ["This action is unauthorized": This message will be returned if a rejected account tries to send a request to the email endpoint.]
 */


class MailerSendController extends Controller
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('MAILERSEND_API_KEY');
        $this->client = new Client([
            'base_uri' => 'https://api.mailersend.com/v1/',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function sendEmail($to, $subject, $content)
    {
        // USAGE;
        /**
            use App\Http\Controllers\MailerSendController;
            $controller = new MailerSendController();
            $response = $controller->sendEmail('app@vistoria.plus', 'Test Subject', 'Test Content');
         */
        try {
            $response = $this->client->post('email', [
                'json' => [
                    'from' => [
                        'email' => env('MAIL_FROM_ADDRESS'),
                        'name' => env('MAIL_FROM_NAME'),
                    ],
                    'to' => [
                        ['email' => $to],
                    ],
                    'subject' => $subject,
                    'html' => $content,
                ],
            ]);

            return json_decode((string) $response->getBody(), true);

        } catch (\Exception $e) {
            // Log the exception
            Log::error('Email sending failed: ' . $e->getMessage());

            if ($e instanceof \GuzzleHttp\Exception\ClientException) {
                // Log the response body for client exceptions
                Log::error('Response body: ' . $e->getResponse()->getBody()->getContents());
            }
            return [
                'error' => 'Failed to send email',
                'message' => $e->getMessage()
            ];
        }
    }


}
