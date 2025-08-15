<?php

namespace App\Traits\Payment;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

trait AsasPayment
{

    public function getPay($amount, $ref_id)
    {
        try {
                $response = Http::withHeaders([
                    "access_token" => env('ACCCESS_TOKEN_ASAS'),
                    "Content-Type" => "application/json"
                ])->post('https://api.asaas.com/v3/pix/qrCodes/static', [
                    "addressKey"        => env('ADDRESS_KEY_ASAS'),
                    "description"       => "Pagamento Créditos acessos",
                    "value"             => $amount,
                    "format"            => "ALL",
                    "expirationDate"    => Carbon::now()->addDay()->format("Y-m-d H:i:s"),
                    "expirationSeconds" => null,
                    "allowsMultiplePayments" => false,
                    "externalReference"  => $ref_id
                ]);

                $data = json_decode($response->getBody());

                $array['reference_code'] = $data->externalReference;
                $array['content'] = $data->payload;
                $array['requestNumber'] = $data->externalReference;
                $array['image_base64'] = $data->encodedImage;

                return $data->payload;

            } catch (\Throwable $exception) {
                Log::debug($exception->getMessage());
                return response()->json(["message" => "Erro ao gerar o deposito"], 400);
            }

    }

}
