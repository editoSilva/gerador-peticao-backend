<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TesteOpenIa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'app:teste-open-ia';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $apiKey = env('OPENAI_API_KEY'); // Certifique-se de que a chave está no .env

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4.1', // Você pode trocar para outro modelo
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'Escreva um pequeno texto de teste para verificar a API do OpenAI.'
                ]
            ],
            'max_tokens' => 150,
        ]);

        dd($response->body());
            // if ($response->failed()) {
            //     return response()->json([
            //         'success' => false,
            //         'error' => $response->body()
            //     ], $response->status());
            // }

            // return response()->json([
            //     'success' => true,
            //     'data' => $response->json()
            // ]);
    
    }
}
