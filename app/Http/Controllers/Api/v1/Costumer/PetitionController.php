<?php

namespace App\Http\Controllers\Api\v1\Costumer;

use App\Models\Petition;
use App\Services\GptService;
use Illuminate\Http\Request;
use App\Models\Jurisprudence;
use App\Mail\PetitionGenerated;
use App\Services\GeminiService;
use Barryvdh\DomPDF\Facade\Pdf;
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

 

        // Montar prompt com os dados do cliente para a geração da petição
        $fullPrompt = <<<EOT
            Crie uma petição simples com base no Código de Defesa do Consumidor (CDC), para ser usada diretamente no Juizado Especial Cível, sem necessidade de advogado.

            Use as seguintes informações:

            Dados do Cliente:
            - Nome Completo: {$data['nome_completo']}
            - CPF: {$data['cpf']}
            - RG: {$data['rg']}
            - Órgão Expedidor: {$data['orgao_expedidor']}
            - Estado Civil: {$data['estado_civil']}
            - Profissão: {$data['profissao']}
            - Endereço: {$data['endereco']}
            - Cidade: {$data['cidade']}
            - Estado: {$data['estado']}
            - CEP: {$data['cep']}
            - Razao Social: {$data['razao_social']}
            - CNPJ: {$data['cnpj']}

            Requerido:
            - {$data['requerido']}

            Descrição do Caso:
            {$data['prompt']}

            Jurisprudências Relevantes:
            {$jurisprudences}

        
        Requisitos:
        1. Pegue o endereço do cliente (cidade e estado) para definir a comarca na petição. Deixe esse campo em maiúsculo e centralizado. Exemplo:  
        'EXCELENTÍSSIMO SENHOR JUIZ DE DIREITO DA [número ou nome] VARA DO SISTEMA DOS JUIZADOS CÍVEIS DO FORO DA COMARCA DE [nome da comarca] DO [Estado da comarca]'.

        2. Utilize a informação do requerido e coloque os dados do mesmo na ação. Busque os dados de CNPJ e razão social na internet, a moeda é Real (R$).

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

        if($request->type == 'cdc') {

            $promptRes = <<<EOT
            Com base no endereço do autor e no conteúdo abaixo, diga de forma curta, objetiva e prática onde o cliente deve ir para ajuizar a ação no Juizado Especial Cível. Inclua:
            O nome e o endereço do juizado mais próximo;
            O que ele deve levar em mãos (documentos);
            Dicas breves para o atendimento presencial;
            Um tom acessível e direto, como se fosse uma orientação passo a passo.
            Endereço do autor: {$data['endereco']}, {$data['cidade']}, {$data['estado']}
            Petição:
            {$generatedText}
            EOT;
            $generateLocation = app(GptService::class)->generatePetitionLocation($promptRes);

        }
        if($request->type == 'trans') {
            $promptText = "Com base no órgão autuador '{$data['orgao_autuador']}' e no estado '{$data['estado']}', onde o cidadão deve entrar com recurso da multa de trânsito,  mostre o local onde devo levar esse documento ou o procedimento que preciso fazer??";
            $generateLocation = app(GeminiService::class)->generatePetition($promptText);
        }

        $rand = rand(18575557, 99999999);

        $petition = Petition::create([
            'ref_id' => $rand+time()+time()+rand(15475,99999),
            'type' => $request->type,
            'content' => $generatedText,
            'input_data' => $data,
            'local_delivery' => $generateLocation,
        ]);

        // Preparar anexos (imagens em base64)
        $imagesBase64 = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $imagesBase64[] = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file));
            }
        }

        // Gerar PDF
        $pdf = Pdf::loadView('pdf.petition_generated', [
            'content' => $generatedText, 
            'name' => $data['nome_completo'],
            'cidade' => $data['cidade'],
            'estado' => $data['estado'],
            'attachments' => $imagesBase64,
        ]);

        $pdfPath = 'petitions/peticao_n_' . $petition->ref_id . '.pdf';

        Storage::disk('s3')->put($pdfPath, $pdf->output(), 'public');

        // Gerar a URL pública
        $pdfUrl = Storage::disk('s3')->url($pdfPath);

        // Atualizar a petição com a URL
        $petition->update([
            'pdf_url' => $pdfUrl,
        ]);
   
        $fileName = 'peticao_'.$petition->ref_id . '.pdf';
        $caption = 'peticao_n_' . $petition->ref_id;

        //Job
       Mail::to($data['email'])->send(
            new PetitionGenerated(
                name: $data['nome_completo'],
                pdfUrl: $pdfPath, // relativo, ex: "petitions/peticao_n_123.pdf"
                fileName: $fileName,
                number: $petition->ref_id,
                description: $petition->local_delivery
            )
        );

        $textoWhats = "*Enviamos para o seu email: {$data['email']},  as informações a respeito do pedito Nº {$petition->ref_id}*";
        
        $this->serviceEvolution->sendMenssageText($request->phone, $textoWhats);

    // return $pdf->output();
// 
    //    $this->serviceEvolution->sendMenssageText($request->phone, 'Segue o link da sua petição: ' . $pdfUrl);
        $this->serviceEvolution->sendMessagePdf($request->phone, $pdfUrl, $fileName, $caption);

        return response()->json($petition);

    }

    public function aplicarNegritoEmCaixaAlta(string $texto): string {
        // Regex que captura palavras/frases em caixa alta (mínimo 2 letras) e envolve em <strong>
        return preg_replace_callback('/\b([A-ZÁÉÍÓÚÇ]{2,}(?:\s[A-ZÁÉÍÓÚÇ]{2,})*)\b/u', function ($matches) {
            return '<strong>' . $matches[0] . '</strong>';
        }, $texto);
    }

    
}
