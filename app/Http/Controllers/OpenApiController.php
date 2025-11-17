<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OpenApiController extends Controller
{
    public function show(Request $request)
    {
        $openApiPath = storage_path('api-docs/api-docs.json');

        if (!file_exists($openApiPath)) {
            return response()->json(['error' => 'OpenAPI file not found'], 404);
        }

        $openApi = json_decode(file_get_contents($openApiPath), true);

        $openApi['servers'] = [
            [
                'url' => 'http://localhost:8000/api',
                'description' => 'Local Development Server'
            ],
            [
                'url' => env('APP_URL') . '/api',
                'description' => 'Production Server'
            ]
        ];

        return response()->json($openApi, 200, [
            'Content-Type' => 'application/json'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
