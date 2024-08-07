<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ScenexImageController extends Controller
{
    protected $connection = 'vpAppTemplate';

    protected $promptperfectSecret = 'XXXXXX';
    protected $scenexApiKey = 'R5fwQsUd7HNrJ5Ke2wIn:f2c609ed87307559538f5aa8fb2dcdaefb675f43e6c0e278bc421198e6d800b0';
    protected $rationaleSecret = 'XXXXXX';
    protected $jinachatSecret = 'XXXXXX';
    protected $bestbannerSecret = 'XXXXXX';

    public function submit(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:jpeg,jpg|max:5120',
            ]);

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $text = $request->input('text');

                if (!$file) {
                    return response()->json(['message' => 'Please select an image.'], 400);
                }

                if (!$text) {
                    return response()->json(['message' => 'Please enter some text.'], 400);
                }

                $config = config("database.connections.{$this->connection}");
                $dbName = $config['database'];
                $folder = 'scenex';

                $path = "{$dbName}/" . date('Y') . '/' . date('m') . '/' . $folder;

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

                //$imageUrl = asset("storage/{$filePath}");
                $imageUrl = 'https://vistoria.plus/media/uploads/1/2023/10/29d3ef7d.jpg';

                $client = new Client();
                $response = $client->post('https://api.scenex.jina.ai/v1/describe', [
                    'headers' => [
                        'x-api-key' => $this->scenexApiKey,
                        'content-type' => 'application/json',
                    ],
                    'json' => [
                        'data' => [
                            'image' => $imageUrl,
                            'algorithm' => 'Jelly',
                            'features' => ['high_quality', 'question_answer'],
                            'question' => $text,
                            'languages' => ['pt'],
                            'style' => 'Concise'
                        ],
                    ]
                ]);

                $result = json_decode($response->getBody(), true);

                return response()->json([
                    'message' => 'Image submitted successfully.',
                    'results' => $result,
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Arquivo não fornecido'], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

}
