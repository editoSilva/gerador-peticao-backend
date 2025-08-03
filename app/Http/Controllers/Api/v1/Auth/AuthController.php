<?php

namespace App\Http\Controllers\Api\v1\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
       
        try {
            // Primeiro, tenta fazer login com as credenciais
            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'data' => [
                        'success' => false,
                        'message' => 'Login ou senha inválidos!'
                    ]
                ], 401);
            }

            // Se o login for bem sucedido, obtém o usuário
            $user = Auth::user();

            
            // Cria um novo token
            $token = $user->createToken('auth-token', ['*'])->plainTextToken;

          

            return response()->json([
                'data' => [
                    'success' => true,
                    'message' => 'User logged in successfully',
                    'token' => $token,
                    'user' => $user
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                "data" => [
                    "success" => false,
                    "error" => $e->getMessage()
                ]
            ], 401);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                "data" => [
                    "success" => true,
                    "message" => 'Logged out successfully'
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "data" => [
                    "success" => false,
                    "error" => $e->getMessage()
                ]
            ], 500);
        }
    }

    public function perfil(Request $request)
    {
        try {
            $user = $request->user();
            return $user;
        } catch (\Exception $e) {                           
            return response()->json([
                "data" => [
                    "success" => false,
                    "error" => $e->getMessage()
                ]
            ], 500);
        }
    }

}
