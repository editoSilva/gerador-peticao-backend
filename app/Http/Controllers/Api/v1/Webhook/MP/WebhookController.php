<?php

namespace App\Http\Controllers\Api\v1\Webhook\MP;

use Illuminate\Http\Request;
use App\Jobs\GeneratePetition;
use App\Models\PetitionRequest;
use App\Http\Controllers\Controller;

class WebhookController extends Controller
{
    private $petitionRequest;

    public function __construct(PetitionRequest $petitionRequest)
    {
        $this->petitionRequest = $petitionRequest;
    }

    public function updatePayment(Request $request)
    {
        $petition = $this->petitionRequest->where('ref_id', $request->id)->with('attachments')->first();

        if($petition && $petition->status === 'pending') 
        {
            GeneratePetition::dispatch($petition, 'payment');

            return response()->json([
                'message' => 'Enviado'
            ], 201);
          
        }
    }
}
