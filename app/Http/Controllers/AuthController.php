<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            if (empty($request->name)) {
                return response()->json(['success' => false, 'message' => 'Nome obrigatório'], 400);
            }

            if (empty($request->email)) {
                return response()->json(['success' => false, 'message' => 'Email obrigatório'], 400);
            }

            if (empty($request->password)) {
                return response()->json(['success' => false, 'message' => 'Senha obrigatória'], 400);
            }

            $existe = DB::table('users')->where('email', $request->email)->first();
            if ($existe) {
                return response()->json(['success' => false, 'message' => 'Email já cadastrado'], 400);
            }

            $userId = DB::table('users')->insertGetId([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuário criado com sucesso',
                'user_id' => $userId
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao criar usuário'], 500);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['success' => false, 'message' => 'Email ou senha inválidos'], 401);
        }

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => auth()->user()
        ]);
    }

    public function me()
    {
        return response()->json([
            'success' => true,
            'data' => auth()->user()
        ]);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso'
        ]);
    }
}