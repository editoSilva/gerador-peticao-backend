<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GptService
{
    public function generatePetition(string $prompt): string
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4.1',
            'store' => true,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        return $response->json('choices.0.message.content') ?? 'Erro na resposta da IA';
    }
}
