<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurisprudence;
use Illuminate\Http\Request;

class JurisprudenceController extends Controller
{
    // Listar jurisprudências com busca e limite de 20 registros
    public function index(Request $request)
    {
        $search = $request->query('search');        

        $query = Jurisprudence::query();

        if ($search) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('summary', 'like', "%{$search}%")
                ->orWhere('full_text', 'like', "%{$search}%")
                ->orWhere('keywords', 'like', "%{$search}%");
        }

        return response()->json($query->orderByDesc('judgment_date')->paginate($request->per_page ?? 30));
    }

    // Criar uma nova jurisprudência
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string', // Tipo de jurisprudência (ex: Acórdão, Decisão, etc.)
            'title' => 'required|string',
            'summary' => 'nullable|string',
            'full_text' => 'required|string',
            'court' => 'nullable|string',
            'case_number' => 'nullable|string',
            'judgment_date' => 'nullable|date',
            'reporting_judge' => 'nullable|string',
            'keywords' => 'nullable|string',
            'source' => 'nullable|string',
        ]);

        $jurisprudence = Jurisprudence::create($data);

        return response()->json($jurisprudence, 201);
    }

    // Exibir uma jurisprudência específica pelo id
    public function show($id)
    {
        $jurisprudence = Jurisprudence::find($id);

        if (!$jurisprudence) {
            return response()->json(['message' => 'Jurisprudence not found'], 404);
        }

        return response()->json($jurisprudence);
    }

    // Atualizar uma jurisprudência existente pelo id
    public function update(Request $request, $id)
    {
        $jurisprudence = Jurisprudence::find($id);

        if (!$jurisprudence) {
            return response()->json(['message' => 'Jurisprudence not found'], 404);
        }

        $data = $request->validate([
            'title' => 'sometimes|required|string',
            'summary' => 'nullable|string',
            'full_text' => 'sometimes|required|string',
            'court' => 'nullable|string',
            'case_number' => 'nullable|string',
            'judgment_date' => 'nullable|date',
            'reporting_judge' => 'nullable|string',
            'keywords' => 'nullable|string',
            'source' => 'nullable|string',
        ]);

        $jurisprudence->update($data);

        return response()->json($jurisprudence);
    }

    // Deletar uma jurisprudência pelo id
    public function destroy($id)
    {
        $jurisprudence = Jurisprudence::find($id);

        if (!$jurisprudence) {
            return response()->json(['message' => 'Jurisprudence not found'], 404);
        }

        $jurisprudence->delete();

        return response()->json(['message' => 'Jurisprudence deleted successfully']);
    }
}
