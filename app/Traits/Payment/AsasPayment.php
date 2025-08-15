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
                    "access_token" => '$aact_prod_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OmExYWFiOTNiLWIzY2EtNGMwYi1iOWNmLWY3NGEyNzhkYjk3NTo6JGFhY2hfZDU1OWFmNDMtYWY3NC00MzI1LWJhNzctZjY3MmY5NTMwNDNm',
                    "Content-Type" => "application/json"
                ])->post('https://api.asaas.com/v3/pix/qrCodes/static', [
                    "addressKey"        => "eb66ab1b-1270-47af-b907-fb4602f0d334",
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

    public function updatePayment($request)
    {
        if ($request['event'] == "PAYMENT_RECEIVED" && $request['payment']['status'] == "RECEIVED")
        {

        // $recharge = $this->recharge->where('id_payment',$request->payment['externalReference'])->first();

        // if( $recharge->status === 'paid') {
        //     return;
        // }
        //  $recharge->status = 'paid';
        //  $recharge->date_payment = Carbon::now()->format("Y-m-d H:i:s");
        //  $recharge->save();

        //  //Altera a Wallet da Loja
        //  $wallet = $this->wallet->where('store_id', $recharge->store_id)->first();
        //  $wallet->credit =  $wallet->credit + $recharge->nuber_credit;
        //  $wallet->save();

        $transaction = Transaction::withoutGlobalScope(TenantScope::class)->where('transaction_id', $request['payment']['externalReference'])->first();

        if(!$transaction) {
            return response()->json([
                'message' => 'Transação não encontrada'
            ], 200);
        }

        if($transaction) {

            if($transaction->status === 'paid') {
                return response()->json([
                    'message' => 'Pagamento já foi pago'
                ], 200);
            }

            //Regra de processamento Taxas:
            // return $this->processPaymentFeesPix($transaction);

            $transaction->paymented_at = Carbon::now()->toDateTimeString();
            $transaction->status = 'paid';
            $transaction->save();

            // $this->payments->paymentPaid($transaction, 'payment_paid');

            $wallet = Wallet::where('seller_id', $transaction->seller_id)->first();

            if(!$wallet) {
                return response()->json([
                    'message' => 'Carteira não encontrada'
                ], 404);
            }

            $wallet->balance += $transaction->amount;
            $wallet->save();

             //Disparo de Job para Webhooks internos
             DispatchWebhookJob::dispatch('payment_paid', $transaction)->onQueue('webhooks');


            return response()->json(['message' => 'Transação processada com sucesso'], 200);

        }

        }
    }



}
