<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\PhotoAi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiImageController extends Controller
{
    public function generate(Request $request)
    {
        $photoId = $request->input('photo_id');

        $photo = Photo::find($photoId);

        if (!$photo) {
            return response()->json(['status' => 'error', 'message' => 'Photo not found'], 404);
        }

        $folder_code = $photo->code;
        $uuid        = $photo->uuid;
        $imagePath = public_path($photo->image);

        if (!file_exists($imagePath)) {
            return response()->json(['status' => 'error', 'message' => 'File not found'], 404);
        }

        $aiFileName = "{$uuid}_a_i.webp";
        $aiSavePath = public_path("properties/{$folder_code}/{$aiFileName}");

        // If already exists
        if (file_exists($aiSavePath)) {
            return response()->json(['status' => 'exist', 'message' => 'AI image already exists']);
        }

        // Stability API
        $url    = 'https://api.stability.ai/v2beta/stable-image/control/structure';
        $apiKey = "sk-lGnLTTdaybgJgCRz3FTvKyYf0HbZrCxMuEelY2bGg2ekKoaU";

        $data = [
            'prompt'             => "add office furniture, some chairs and tables, modern Design, keep walls original structure, keep original room length",
            'negative_prompt'    => 'dont change camera angle, dont change room dimensions,dont change wall boundaries ,dont change floor length, dont change ceiling, dont change structure',
            'control_strength'   => 0.9,
            'style_preset'       => '3d-model',
            'sampling_method'    => 'Euler a',
            'sampling_steps'     => 40,
            'cfg_scale'          => 10,
            'denoising_strength' => 0.5,
            'output_format'      => 'webp',
        ];

        try {
            $response = Http::retry(3, 5000)
                ->timeout(120)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Accept'        => 'image/*',
                ])->attach(
                    'image', file_get_contents($imagePath), basename($imagePath)
                )->post($url, $data);

            if ($response->failed()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'API error',
                    'raw'     => $response->body()
                ], 500);
            }

            file_put_contents($aiSavePath, $response->body());

            PhotoAi::create([
                'photo_id' => $photo->id,
                'img_name' => "properties/{$folder_code}/{$aiFileName}",
                'preset'   => '3d-model',
                'style'    => 'modern office',
                'prompt'   => 'Interior Decor',
                'code'     => $folder_code,
            ]);

            return response()->json(['status' => 'success', 'message' => 'AI image generated']);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'API timeout or unreachable',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
