<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class VitalsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        if (! is_array($data) || empty($data['name'])) {
            return response('', 204);
        }

        Log::channel('stderr')->info('web_vital', [
            'metric' => $data['name'],
            'value' => $data['value'] ?? null,
            'rating' => $data['rating'] ?? null,
            'url' => $data['url'] ?? null,
        ]);

        return response('', 204);
    }
}
