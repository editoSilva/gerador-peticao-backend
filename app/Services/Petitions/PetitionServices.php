<?php

namespace App\Services\Petitions;

use App\Models\Jurisprudence;
use App\Models\PetitionRequest;
use App\Models\PetitionAttachment;
use Illuminate\Support\Facades\Storage;
use App\Services\Payments\PaymentServices;

class PetitionServices
{
    public function generatedPetition($request)
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
                'requerido' => 'string',
                'email' => 'required|email',
                'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,png',
                'razao_social' => 'string',
                'cnpj' => 'string',
                'type' => 'required|string',
                'phone' => 'required|string',
                'placa' => 'string',
                'data' => 'string',
                'local' => 'string',
                'infracao' => 'string',
                'numero_auto_infra' => 'string',
                'orgao_atuador' => 'string',
            ]);

            
        if($request->type === 'cdc') {
            // Buscar jurisprudências do banco e incluir no prompt
            $jurisprudences = Jurisprudence::where('type', $request->type)->get()->map(function ($juri, $i) {
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
        }
       
        $data['ref_id'] =  rand(215475,99999)+time()+time()+rand(15475,99999);
        //Pega o valor da petição
        $data['price'] = 50.00;
        //faz o processamento com o banco
        $data['qr_code'] = app()->make(PaymentServices::class)->pay($data['price'], $data['ref_id']);

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

        return $petitionRequest;
        
    } 
}
