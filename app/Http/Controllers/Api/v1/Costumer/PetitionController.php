<?php

namespace App\Http\Controllers\Api\v1\Costumer;

use App\Models\Petition;
use App\Services\GptService;
use Illuminate\Http\Request;
use App\Models\Jurisprudence;
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

        // return $this->serviceEvolution->sendMenssageText($request->phone, 'Segue o link da sua petição: https://systechtecnologia.s3.amazonaws.com/petitions/peticao_n_3604590023.pdf');

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

        // return $jurisprudences;

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
            1. pegue o endereço do clientes cidade estado para definir a comarca na petição, deixe esse campo em maiusculo e centralizado. Ex: 'EXCELENTÍSSIMO SENHOR JUIZ DE DIREITO DA [número ou nome] VARA DO SISTEMA DOS JUIZADOS CÍVEIS DO FORO DA COMARCA DE [nome da comarca] DO [Estado da comarca]'
            2. utilize a informação do requerido e coloque os dados do mesmo na ação,  busque os dados de cnpj e razação social na internet, a moeda é real R$
            // 3. Basei a ação no artigo 186 do cdc com um exceente fundamento jurídico a peça. 
            4. Estrutura com: cabeçalho, qualificação do consumidor, exposição dos fatos, fundamentos jurídicos com base no CDC, e pedidos. CALCULE O VALOR DA CAUSA PEÇA UM VALOR ALTO QUE CHEGUE NO TETO DE 40 SALÁRIOS MINIMIOS VIGENTES, REFERENTE AO PEDIDO QUE O CLIENTE SOLICITOU DA DESCRIÇÃO DOS FATOS, FAÇA ISSO DE FORMA BASEADA NO CDC, SE BASEI NO ART 42 DO CDC PARA FAZER UM CALCULO QUE DER UM VALOR ALTO PARA O REQUERENTE
            5. Citar os artigos do CDC aplicáveis (como má prestação de serviço, serviço essencial, direito à informação, etc), Basei a ação no artigo 186.
            6. Incluir pedido de indenização por danos morais, se cabível.
            7. Inserir ao final: "Termos em que, pede deferimento.", seguido da data e nome como assinatura.
            8. Utilize uma excelente linguagem
            9. Calcule o valor da causa com base no valor do pedido, se não houver valor, coloque o valor de 1.000,00
            10. A petição deve ser escrita de forma clara, objetiva e acessível, sem jargões jurídicos complexos.
            11. A petição deve ser escrita em português brasileiro, com correção gramatical e ortográfica.
            12. A petição deve ser formatada de forma adequada, com parágrafos bem definidos e espaçamento adequado.
            13. A petição deve conter a data e o local de onde está sendo feita, com o nome do cliente como assinatura.
            14. A petição deve ser escrita de forma que qualquer pessoa possa entender, sem necessidade de conhecimento jurídico prévio.
            15. A petição deve ser escrita de forma que possa ser enviada por e-mail, sem necessidade de formatação adicional.
            16. remova o nome  no fim da petição e data também.
            17. Coloque uma menssão que as provas estão em anexo em imagens.
            A linguagem deve ser clara, objetiva e acessível para qualquer pessoa.
            quero que a petição seja real sem caracteres especiais, separe por paragráfo corretamente e preciso que tenha a data e  o local digitado que ja venha impresso
            EOT;


        $generatedText = app(GptService::class)->generatePetition($fullPrompt);

        if($request->type == 'cdc') {
            $generateLocation = app(GptService::class)->generatePetitionLocation(
                "Qual o local e comarca da petição com base no documento gerado: {$generatedText}, mostre o local onde devo levar esse documento ou o procedimento que preciso fazer?"
            );

        }
        if($request->type == 'trans') {

         $orgao = strtolower($data['orgao_autuador'] ?? '');



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
        $pdf = Pdf::loadView('emails.petition_generated', [
            'content' => $generatedText, 
            'name' => $data['nome_completo'],
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
   
       $this->serviceEvolution->sendMenssageText($request->phone, $petition->local_delivery);

       $this->serviceEvolution->sendMenssageText($request->phone, 'Segue o link da sua petição: ' . $pdfUrl);


        return response()->json($petition);

    }
}
