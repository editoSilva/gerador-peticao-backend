<?php

namespace App\Http\Controllers\Api\v1\Costumer;

use App\Models\Petition;
use App\Services\GptService;
use Illuminate\Http\Request;
use App\Models\Jurisprudence;
use App\Mail\PetitionGenerated;
use App\Models\PetitionRequest;
use App\Services\GeminiService;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PetitionAttachment;
use App\Services\EvolutionService;
use App\Mail\PetitionGeneratedMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Services\Payments\PaymentServices;
use App\Services\Petitions\PetitionServices;


class PetitionController extends Controller
{
    private $serviceEvolution;
    private $petitionServices;

    public function __construct(
        EvolutionService $serviceEvolution, 
        PetitionServices $petitionServices,
       
    )
    {
        $this->serviceEvolution = $serviceEvolution;
        $this->petitionServices = $petitionServices;
    }
  
    public function store(Request $request)
    {

        return $this->petitionServices->generatedPetition($request);

    }

    public function aplicarNegritoEmCaixaAlta(string $texto): string {
        // Regex que captura palavras/frases em caixa alta (mínimo 2 letras) e envolve em <strong>
        return preg_replace_callback('/\b([A-ZÁÉÍÓÚÇ]{2,}(?:\s[A-ZÁÉÍÓÚÇ]{2,})*)\b/u', function ($matches) {
            return '<strong>' . $matches[0] . '</strong>';
        }, $texto);
    }

    
}
