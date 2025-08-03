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


class PetitionController extends Controller
{
    private $serviceEvolution;

    public function __construct(EvolutionService $serviceEvolution)
    {
        $this->serviceEvolution = $serviceEvolution;
    }
  
    public function store(Request $request)
    {
        $data = $request->validate([
            'prompt' => 'required|string',                                            
            'nome_completo' => 'required|string',
            'cpf' => 'required|string',
            'rg' => 'required|string',
            'orgao_expedidor' => 'required|string',
            'estado_civil' => 'required|string',
            'profissao' => 'required|string',
            'endereco' => 'required|string',
            'cidade' => 'required|string',
            'estado' => 'required|string',
            'cep' => 'required|string',
            'requerido' => 'required|string',
            'email' => 'required|email',
            'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,png',
            'razao_social' => 'required|string',
            'cnpj' => 'required|string',
            'type' => 'required|string',
            'phone' => 'required|string'
        ]);

        // Buscar jurisprudências do banco e incluir no prompt
        $jurisprudences = Jurisprudence::all()->map(function ($juri, $i) {
            return (
                ($i + 1) . ". " . $juri->title . "\n"
                . "Resumo: " . ($juri->summary ?? 'N/A') . "\n"
                . "Tribunal: " . ($juri->court ?? 'N/A') . "\n"
                . "Número do Processo: " . ($juri->case_number ?? 'N/A') . "\n"
                . "Data do Julgamento: " . ($juri->judgment_date ?? 'N/A') . "\n"
                . "Relator: " . ($juri->reporting_judge ?? 'N/A') . "\n"
                . "Palavras-chave: " . ($juri->keywords ?? 'N/A') . "\n"
                . "Fonte: " . ($juri->source ?? 'N/A') . "\n"
                . "Texto Completo:\n" . $juri->full_text
            );
        })->implode("\n\n");

        //Jurisprudência
        $data['jurisprudences'] = $jurisprudences;

        $data['ref_id'] =  rand(215475,99999)+time()+time()+rand(15475,99999);
        //Pega o valor da petição
        $data['price'] = 50.00;
        //faz o processamento com o banco
        $data['qr_code'] = '00020126580014BR.GOV.BCB.PIX0136c1a2b3c4d5e6f7g8h9i0123456789015204000053039865405100.005802BR5925Nome do Recebedor6009Sao Paulo61080540900062070503***6304A13A';

        $petitionRequest =  PetitionRequest::create($data);

        if($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Salvar arquivo no S3, na pasta 'attachments'
                $path = $file->store('attachments', 's3');
    
                // Opcional: definir o arquivo como público (depende do seu bucket e regra)
                Storage::disk('s3')->setVisibility($path, 'public');
    
                // Salvar registro no banco
                PetitionAttachment::create([
                    'petition_request_id' => $petitionRequest->id,
                    'file_path' => $path, // caminho no bucket S3
                    'file_type' => $file->getClientMimeType(),
                ]);
            }
        }

        if(!$petitionRequest) {
            return response()->json([
                'message' => 'error',
            ], 400);
        }

        return response()->json([
                'ref_id'  => $petitionRequest->ref_id,  
                'qr_code' => $petitionRequest->qr_code,
                'price'   => $petitionRequest->price,  
        ]);

    }

    public function aplicarNegritoEmCaixaAlta(string $texto): string {
        // Regex que captura palavras/frases em caixa alta (mínimo 2 letras) e envolve em <strong>
        return preg_replace_callback('/\b([A-ZÁÉÍÓÚÇ]{2,}(?:\s[A-ZÁÉÍÓÚÇ]{2,})*)\b/u', function ($matches) {
            return '<strong>' . $matches[0] . '</strong>';
        }, $texto);
    }

    
}
