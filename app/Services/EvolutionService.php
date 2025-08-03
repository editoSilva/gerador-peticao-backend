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

    public function sendMessagePdf($number, $pdfUrl, $fileName, $caption = null)
    {
        $payload = [
            'number' => $number,
            'mediatype' => 'document',        // tudo minúsculo conforme o curl
            'mimetype' => 'application/pdf',  // MIME type correto para PDF
            'caption' => $caption ?? '',
            'media' => $pdfUrl,                // URL pública do PDF ou base64
            'fileName' => $fileName,
            'delay' => 100,
            'linkPreview' => false,
            // 'mentionsEveryOne' => false, // se quiser
            // 'mentioned' => [],            // se quiser mencionar
            // 'quoted' => [],               // para responder mensagem específica
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'apikey' => env('WHATSBOOT_API_KEY'),
        ])->post('https://whatsboot.partnersrolex.com/message/sendMedia/teste', $payload);

        if ($response->successful()) {
            return $response->json();
        }

        // Se falhar, retorna o erro para debug
        // return [
        //     'success' => false,
        //     'status' => $response->status(),
        //     'error' => $response->body(),
        // ];
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
