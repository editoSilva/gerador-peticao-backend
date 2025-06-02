namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PetitionController extends Controller
{
    public function form()
    {
        return view('petition_form');
    }

    public function gerarPeticao(Request $request)
    {
        $request->validate([
            'descritivo' => 'required|string',
            'documento_usuario' => 'required|file|mimes:pdf,jpg,jpeg,png',
            'conta' => 'required|file|mimes:pdf,jpg,jpeg,png',
        ]);

        // Armazena arquivos
        $pathDocumento = $request->file('documento_usuario')->store('documentos');
        $pathConta = $request->file('conta')->store('contas');

        // Prompt base para o Gemini
        $prompt = "Gere uma petição judicial com base neste descritivo:\n\n" . $request->descritivo . "\n\n" .
                  "Documentos anexados:\n- Documento da pessoa: " . basename($pathDocumento) .
                  "\n- Conta de luz/água: " . basename($pathConta);

        // Envia para Gemini (usando o modelo Gemini 1.5 Pro)
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('GOOGLE_API_KEY'),
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . env('GOOGLE_API_KEY'), [
            'contents' => [[
                'parts' => [['text' => $prompt]]
            ]]
        ]);

        $json = $response->json();
        $peticao = $json['candidates'][0]['content']['parts'][0]['text'] ?? 'Erro ao gerar petição.';

        return view('petition_form', compact('peticao'));
    }
}


use App\Http\Controllers\PetitionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PetitionController::class, 'form'])->name('form');
Route::post('/gerar-peticao', [PetitionController::class, 'gerarPeticao'])->name('gerar.peticao');
# gerador-peticao-backend
