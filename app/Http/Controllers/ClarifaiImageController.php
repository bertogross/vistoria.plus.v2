<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClarifaiImageController extends Controller
{
    protected $connection = 'vpAppTemplate';

    /*
    protected $pat = 'a6871127de264bc284aa06b412132753';
    protected $userId = 'clarifai';
    protected $appId = 'main';
    protected $modelId = 'moderation-recognition';
    protected $modelVersionId = 'aa8be956dbaa4b7a858826a84253cab9';
    */
    // Model general-image-recognition
    // https://clarifai.com/clarifai/main/models/general-image-recognition
    protected $pat = '6140cf7a828c4eddaaf46ed2bdb5bea0';
    protected $userId = 'clarifai';
    protected $appId = 'main';
    protected $modelId = 'general-image-recognition';
    protected $modelVersionId = 'aa7f35c01e0642fda5cf400f543e7c40';

    public function submit(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:jpeg,jpg|max:5120',
            ]);

            if ($request->hasFile('file')) {
                $result = '';

                $file = $request->file('file');
                $text = $request->input('text');

                if (!$file) {
                    return response()->json(['message' => 'Please select an image.'], 400);
                }

                if (!$text) {
                    return response()->json(['message' => 'Please enter some text.'], 400);
                }
                // \Log::info("File object: " . print_r($file, true));  // Log the file object for debugging

                // Get the database name from the connection configuration
                $config = config("database.connections.{$this->connection}");
                $dbName = $config['database'];
                $folder = 'clarifai';

                $path = "{$dbName}/" . $folder . '/' . date('Y') . '/' . date('m');

                // Ensure the directory exists
                if (!Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->makeDirectory($path);
                }
                /*if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }*/

                $filePath = $file->store($path, 'public');
                $absoluteFilePath = Storage::disk('public')->path($filePath);

                if (!file_exists($absoluteFilePath)) {
                    \Log::error("File does not exist at path: $absoluteFilePath");
                    return response()->json(['message' => 'File does not exist.'], 400);
                }

                $base64Image = base64_encode(file_get_contents($absoluteFilePath));

                $client = new Client();
                $response = $client->post('https://api.clarifai.com/v2/models/' . $this->modelId . '/versions/' . $this->modelVersionId . '/outputs', [
                    'headers' => [
                        'Authorization' => 'Key ' . $this->pat,
                        'Accept' => 'application/json',
                    ],
                    'json' => [
                        'user_app_id' => [
                            'user_id' => $this->userId,
                            'app_id' => $this->appId,
                        ],
                        'inputs' => [
                            [
                                'data' => [
                                    'image' => [
                                        'base64' => $base64Image,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]);

                $result = json_decode($response->getBody(), true);

                /*
                return response()->json([
                    'message' => 'Image submitted successfully.',
                    'results' => $result,
                ]);
                */

                // Extract relevant data from Clarifai results
                $concepts = $result['outputs'][0]['data']['concepts'];
                $descriptions = [];
                foreach ($concepts as $concept) {
                    //$descriptions[] = '- '.$concept['name'] . ': ' . number_format($concept['value'], 4);
                    $descriptions[] = $concept['name'] . ': ' . number_format($concept['value'], 4);
                }
                //$inputText = implode('~~~~', $descriptions);
                $inputText = implode(', ', $descriptions);

                // Send data to GPT-3 for text interpretation
                $gptResponse = $this->generateTextInterpretation($inputText);

                return response()->json([
                    'message' => 'Image submitted successfully.',
                    'results' => $result,
                    'interpretation' => $gptResponse,
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Arquivo não fornecido'], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function generateTextInterpretation($inputText)
    {
        $prompt = '';
        $client = new Client();
        $apiKey = env('OPENAI_API_KEY');

        //$prompt .= "Based on the following concepts identified in an image, provide a detailed interpretation and analysis:\n\n";
        //$prompt .="Extract the relevant information from the response and format it in a way that is easy to human understand.\n\n";
        $prompt .= "Dada a seguinte lista de conceitos e suas porcentagens de presença em uma fotografia:\n";
        //$prompt .= str_replace("~~~~", "\n", $inputText);
        $prompt .= $inputText;
        $prompt .= "\n\nDescreva de forma simples e em português, como se estivesse falando para uma criança, o que essa combinação de conceitos diz sobre o conteúdo e o contexto da fotografia.";
        //$prompt .= "\n\nDescreva de forma simples com até 500 caracteres e em português, o que essa combinação de conceitos diz sobre o conteúdo e o contexto da fotografia.";
        //$prompt .= "\nExplain it as if it were to a child and in Portuguese Brazilian language text.";

        $response = $client->post('https://api.openai.com/v1/engines/davinci/completions', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey,
            ],
            'json' => [
                'prompt' => $prompt,
                'max_tokens' => 300,
            ],
        ]);

        $gptResult = json_decode($response->getBody(), true);
        $interpretation = $gptResult['choices'][0]['text'] ?? 'No interpretation available.';

        return $interpretation;
    }
}
