<?php

namespace App\Services\Payments;

class PaymentServices
{
    public function pay($amount)
    {   
        $qr_code = "00120126580014BR.GOV.BCB.PIX0136c1a2b3c4d5e6f7g8h9i0123456789015204000053039865405100.005802BR5925Nome do Recebedor6009Sao Paulo61080540900062070503***6304A13A";
        
        return $qr_code;
    }
}
