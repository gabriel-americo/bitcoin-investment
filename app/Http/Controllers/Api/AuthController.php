<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Exception;

class AuthController extends Controller
{

    public function __construct(
        protected AuthService $authService
    ) {}

    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|min:6',
            ], [
                'name.required' => 'O nome é obrigatório.',
                'name.max' => 'O nome deve ter no mão de 255 caracteres.',
                'email.required' => 'O email é obrigatório.',
                'email.email' => 'O email deve ser válido.',
                'password.required' => 'A senha é obrigatória.',
            ]);

            $user = $this->authService->register($validated);

            return response()->json([
                'message' => 'Usuário registrado com sucesso',
                'data' => new UserResource($user)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao registrar usuário. Tente novamente.',
                'data' => []
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ], [
                'email.required' => 'O email é obrigatório.',
                'email.email' => 'O email deve ser válido.',
                'password.required' => 'A senha é obrigatória.',
            ]);

            $result = $this->authService->login($validated);

            if (!$result['success']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $result['message'],
                    'data' => []
                ], 401);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Login realizado com sucesso.',
                'data' => [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token']
                ]
            ])->withCookie($result['cookie']);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ocorreu um erro no login. Tente novamente.',
                'data' => []
            ], 500);
        }
    }

    public function logout()
    {
        try {
            $this->authService->logout();

            return response()->json([
                'status' => 'success',
                'message' => 'Logout realizado com sucesso.',
                'data' => []
            ])->withCookie(Cookie::forget('jwt'));
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao realizar logout. Tente novamente.',
                'data' => []
            ], 500);
        }
    }
}
