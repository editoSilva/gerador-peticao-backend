<?php

namespace App\Jobs;


use App\Models\Petition;
use App\Services\GptService;
use App\Mail\PetitionGenerated;
use App\Models\PetitionRequest;
use App\Services\GeminiService;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\EvolutionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class GeneratePetition implements ShouldQueue
{
    use Queueable;

    public $petition;
    public $serviceEvolution;
    public $origin;

    /**
     * Create a new job instance.
     */
    public function __construct(PetitionRequest $petition, $origin)
    {
        $this->petition = $petition;
        $this->serviceEvolution = app()->make(EvolutionService::class);
        $this->origin = $origin;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $petition = $this->petition;

        info($petition);
        if($petition->type == 'cdc') {
            // Montar prompt com os dados do cliente para a geração da petição
            $fullPrompt = <<<EOT
            Crie uma petição simples com base no Código de Defesa do Consumidor (CDC), para ser usada diretamente no Juizado Especial Cível, sem necessidade de advogado.

            Use as seguintes informações:

            Dados do Cliente:
            - Nome Completo: {$petition->nome_completo}
            - CPF: {$petition->cpf}
            - RG: {$petition->rg}
            - Órgão Expedidor: {$petition->orgao_expedidor}
            - Estado Civil: {$petition->estado_civil}
            - Profissão: {$petition->profissao}
            - Endereço: {$petition->endereco}
            - Cidade: {$petition->cidade}
            - Estado: {$petition->estado}
            - CEP: {$petition->cep}
            - Razao Social: {$petition->razao_social}
            - CNPJ: {$petition->cnpj}

            Requerido:
            - {$petition->requerido}

            Descrição do Caso:
            {$petition->prompt}

            Jurisprudências Relevantes:
            {$petition->jurisprudences}

        
            Requisitos:
            1. Pegue o endereço do cliente (cidade e estado) para definir a comarca na petição. Deixe esse campo em maiúsculo e centralizado. Exemplo:  
            'EXCELENTÍSSIMO SENHOR JUIZ DE DIREITO DA [número ou nome] VARA DO SISTEMA DOS JUIZADOS CÍVEIS DO FORO DA COMARCA DE [nome da comarca] DO [Estado da comarca]'.

            2. Utilize a informação do requerido e coloque os dados do mesmo na ação. Busque os dados de CNPJ e razão social na internet, a moeda é Real (R$), preciso que o rg seja formatado assim: 000000-00.

            3. Baseie a ação no artigo 186 do CDC com um excelente fundamento jurídico na peça.

            4. Estruture a petição com: cabeçalho, qualificação do consumidor, exposição dos fatos, fundamentos jurídicos com base no CDC e pedidos.  
            Calcule o valor da causa até 40 salários mínimos vigentes, referente ao pedido que o cliente solicitou na descrição dos fatos. Faça isso de forma baseada no CDC.  
            Se baseie no artigo 42 do CDC para fazer um cálculo que der um valor alto para o requerente, formate o cpf rg ou cnpj de formas com pontos traços de forma bem legível.

            5. Cite os artigos do CDC aplicáveis (como má prestação de serviço, serviço essencial, direito à informação, etc). Baseie a ação no artigo 186.

            6. Inclua pedido de indenização por danos morais, se cabível.

            7. Insira ao final: "Termos em que, pede deferimento.", seguido da data e nome como assinatura.

            8. Utilize uma excelente linguagem.

            9. Calcule o valor da causa com base no valor do pedido; se não houver valor, coloque o valor de R$ 1.000,00.

            10. A petição deve ser escrita de forma clara, objetiva e acessível, sem jargões jurídicos complexos.

            11. A petição deve ser escrita em português brasileiro, com correção gramatical e ortográfica.

            12. A petição deve ser formatada de forma adequada, com parágrafos bem definidos e espaçamento adequado.

            13. A petição deve conter a data e o local de onde está sendo feita, com o nome do cliente como assinatura.

            14. A petição deve ser escrita de forma que qualquer pessoa possa entender, sem necessidade de conhecimento jurídico prévio.

            15. A petição deve ser escrita de forma que possa ser enviada por e-mail, sem necessidade de formatação adicional.

            16. Remova o nome no fim da petição e a data também.

            17. Coloque uma menção que as provas estão em anexo em imagens.

            A linguagem deve ser clara, objetiva e acessível para qualquer pessoa.  
            Quero que a petição seja real, sem caracteres especiais, e que os parágrafos estejam separados corretamente.
            EOT;


             $generatedText = app(GptService::class)->generatePetition($fullPrompt);

     

            $promptRes = <<<EOT
            Com base no endereço do autor e no conteúdo abaixo, diga de forma curta, objetiva e prática onde o cliente deve ir para ajuizar a ação no Juizado Especial Cível. Inclua:
            O nome e o endereço do juizado mais próximo;
            O que ele deve levar em mãos (documentos);
            Dicas breves para o atendimento presencial;
            Um tom acessível e direto, como se fosse uma orientação passo a passo.
            Endereço do autor: {$petition->endereco}, {$petition->cidade}, {$petition->estado}
            Petição:
            {$generatedText}
            EOT;
            $generateLocation = app(GptService::class)->generatePetitionLocation($promptRes);

        }

        if($petition->type == 'trans') {

            $orgao = strtoupper($petition->orgao_atuador);

            $prompt = <<<EOT
            Você é um advogado especialista em Direito de Trânsito.

            No início da petição, escreva:

            Ilustríssimo(a) Senhor(a) Presidente da Junta Administrativa de Recursos de Infrações – {$orgao}

            Ref.: Recurso Administrativo – Auto de Infração nº [{$petition->numero_auto_infra}]

            Com base nas informações abaixo, redija um recurso administrativo para defesa de multa de trânsito. O texto deve ser claro, fundamentado no Código de Trânsito Brasileiro (CTB) e nos princípios constitucionais da ampla defesa, contraditório e devido processo legal. A linguagem deve ser respeitosa e acessível.

            O recurso deve ser **endereçado formalmente ao órgão autuador informado abaixo**, com uma saudação adequada (por exemplo, "Ilustríssimo(a) Senhor(a) Presidente da Junta Administrativa de Recursos de Infrações – {$petition->orgao_autuador}").

            ---

            DADOS DO CONDUTOR:

            - Nome completo: {$petition->nome_completo}
            - CPF: {$petition->cpf}
            - RG: {$petition->rg} ({$petition->orgao_expedidor})
            - Estado civil: {$petition->estado_civil}
            - Profissão: {$petition->profissao}
            - Endereço: {$petition->endereco}, {$petition->cidade}/{$petition->estado}, CEP: {$petition->cep}
            - E-mail: {$petition->email}
            - Telefone: {$petition->phone}

            DADOS DA INFRAÇÃO:

            - Órgão autuador: {$petition->orgao_autuador}
            - Placa do veículo: {$petition->placa}
            - Data da infração: {$petition->data}
            - Local da infração: {$petition->local}
            - Infração descrita: {$petition->infracao}

            DEFESA DO CONDUTOR:

            {$petition->prompt}

            Remova o nome no fim da petição e a data também.

            ANEXOS:
            Se aplicável, foram incluídas provas visuais e documentais (fotos, vídeos, documentos) que ajudam a demonstrar os fatos alegados.

            ---

            Com base nessas informações, redija uma defesa administrativa em primeira pessoa, citando os artigos pertinentes do CTB (como art. 280 e 281), normas do CONTRAN e os princípios constitucionais. Solicite o cancelamento da multa e finalize com um pedido de deferimento do recurso.
            EOT;

            $promptText = "Com base no órgão autuador '{$petition->orgao_autuador}' e no estado '{$petition->estado}', onde o cidadão deve entrar com recurso da multa de trânsito,  mostre o local onde devo levar esse documento ou o procedimento que preciso fazer??";
            
            
            $generateLocation = app(GptService::class)->generatePetitionLocation($promptText);

            $generatedText = app(GptService::class)->generatePetition($prompt);
        }

        $petitionNew = Petition::create([
            'ref_id' => $petition->ref_id,
            'type' => $petition->type,
            'content' => $petition->qr_code,
            'input_data' => $generatedText,
            'local_delivery' => $generateLocation,
            'origin'        => $this->origin,
            'status'        => 'paid',
            'pdf_url'       =>  $petition->pdf_url
        ]);

        // Preparar anexos (imagens em base64)
        $imagesBase64 = [];

        if (count($petition->attachments) > 0) {
            foreach ($petition->attachments as $attachment) {
                $fileContent = Storage::disk('s3')->get($attachment->file_path);
                $imagesBase64[] = 'data:' . $attachment->file_type . ';base64,' . base64_encode($fileContent);
            }
        }
        // Gerar PDF
        $pdf = Pdf::loadView('pdf.petition_generated', [
            'content' => $generatedText, 
            'name' => $petition->nome_completo,
            'cidade' => $petition->cidade,
            'estado' => $petition->estado,
            'attachments' => $imagesBase64,
        ]);

        $pdfPath = 'petitions/peticao_n_' . $petition->ref_id . '.pdf';

        Storage::disk('s3')->put($pdfPath, $pdf->output(), 'public');

        // Gerar a URL pública
        $pdfUrl = Storage::disk('s3')->url($pdfPath);

        // Atualizar a petição com a URL
        $petitionNew->update([
            'pdf_url' => $pdfUrl,
        ]);
   
        $fileName = 'peticao_'.$petition->ref_id . '.pdf';
        $caption = 'peticao_n_' . $petition->ref_id;

        //Job
        Mail::to($petition->email)->send(
            (new PetitionGenerated(
                name: $petition->nome_completo,
                pdfUrl: $pdfPath,
                fileName: $fileName,
                number: $petition->ref_id,
                description: $petitionNew->local_delivery
            ))->from('contato@direitocidadao.com', "Petição Nº ({$petition->ref_id}), Direito Cidadção")
        );

        $textoWhats = saudacaoPorHorario()."!  *{$petition->nome_completo}*.\nEnviamos para o seu email: {$petition->email}.\nAs informações a respeito do pedito Nº {$petition->ref_id}";        

        $this->serviceEvolution->sendMenssageText($petition->phone, $textoWhats);
        $this->serviceEvolution->sendMessagePdf($petition->phone, $pdfUrl, $fileName, $caption);

        $this->petition->status = 'paid';
        $this->petition->save();

            
    }
}
