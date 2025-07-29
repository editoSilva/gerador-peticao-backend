<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;

class EvolutionService
{
    public function sendMenssageText($number, $text)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'apikey' =>  env('WHATSBOOT_API_KEY')
        ])->post('https://whatsboot.partnersrolex.com/message/sendText/teste', [
            'number' => $number,
            'text' => $text
        ]);

        return $response->successful() ? $response->json() : $response->body();
    }

    public function sendMessagePdf($number, $pdfPath, $fileName = 'documento.pdf', $caption = 'Segue o documento solicitado')
    {
        // Ler o PDF e converter para Base64
        if (!file_exists($pdfPath)) {
            return ['error' => 'Arquivo PDF não encontrado.'];
        }

        $base64 = base64_encode(file_get_contents($pdfPath));

        // Montar payload
        $payload = [
            'number' => $number,
            'options' => [
                'delay' => 0,
                'presence' => 'composing'
            ],
            'mediaMessage' => [
                'mediaType' => 'document',
                'fileName' => $fileName,
                'caption' => $caption,
                'media' => $base64
            ]
        ];

        // Enviar para EvolutionAPI
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'apikey' => env('WHATSBOOT_API_KEY')
        ])->post('https://whatsboot.partnersrolex.com/message/sendMedia/teste', $payload);

        return $response->successful() ? $response->json() : $response->body();
    }

    public function sendImagemUrl($number, $media)
    {
        $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'apikey' =>  env('WHATSBOOT_API_KEY')
            ])->post('https://whatsboot.partnersrolex.com/message/sendMedia/teste', [
                'number' => $number,
                'mediatype' => 'image',
                'mimetype' => 'image/png',
                'caption' => 'Nexus Yield',
                'media' => $media,
                'fileName' => 'Imagem.png'
            ]);

        return $response->successful() ? $response->json() : $response->body();
    }
}
