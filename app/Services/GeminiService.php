<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    public function generatePetition(string $prompt): string
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . env('GOOGLE_API_KEY'), [
            'contents' => [[
                'parts' => [[ 'text' => $prompt ]]
            ]]
        ]);

        return $response->json('candidates.0.content.parts.0.text') ?? 'Erro na resposta da IA';
    }
}
