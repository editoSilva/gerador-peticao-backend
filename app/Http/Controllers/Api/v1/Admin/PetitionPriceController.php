<?php

namespace App\Http\Controllers\Api\v1\Admin;

use Illuminate\Http\Request;
use App\Models\PetitionPrice;
use App\Http\Controllers\Controller;

class PetitionPriceController extends Controller
{
    private $petitionPrice;
    
    public function __construct(PetitionPrice $petitionPrice)
    {
        $this->petitionPrice = $petitionPrice;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->petitionPrice->filter($request->all())->paginate(20);
    }

  

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $price = $this->petitionPrice->create($request->all());

        if(!$price) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => 'Erro ao cadastrar!'
            ]], 400);
        }

        return $price;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $price = $this->petitionPrice->find($id);

        if(!$price) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => 'Não encontrado!'
            ]], 400);
        }

        return $price;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $price = $this->petitionPrice->find($id);

        $update = $price->update($request->all());

        if(!$update) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => 'Não encontrado!'
            ]], 400);
        }

        return $price;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $price = $this->petitionPrice->find($id);

       
        
        if(!$price) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => 'Não encontrado!'
            ]], 400);
        }

        $price->delete();
    }
}
