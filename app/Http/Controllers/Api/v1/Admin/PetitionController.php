<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Models\Petition;
use Illuminate\Http\Request;
use App\Jobs\GeneratePetition;
use App\Http\Controllers\Controller;
use App\Services\Petitions\PetitionServices;

class PetitionController extends Controller
{
    private $petition;
    private $petitionServices;

    public function __construct(Petition $petition, PetitionServices $petitionServices)
    {   
        $this->petition = $petition; 
        $this->petitionServices = $petitionServices;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->petition->filter($request->all())->paginate(20);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $petition = $this->petitionServices->generatedPetition($request);

        GeneratePetition::dispatch($petition, 'admin');
        
        if(!$petition) {
            response()->json([
                'data' => [
                    'success' => false,
                    'message' => 'Petição não criada!'
                ]], 400);
        }

        return response()->json([
            'data' => [
                'sucess' => true,
                'message' => "Petição sendo criada... Nº {$petition->ref_id}"
            ]], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $petition = $this->petition->find($id);

        if(!$petition) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => 'Petição não encontrada!'
                ]
            ],400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
