<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    public function login(array $credentials)
    {
        if (!Auth::attempt($credentials)) {
            return [
                'success' => false,
                'message' => 'Credenciais inválidas.'
            ];
        }

        $user = Auth::user();
        $token = $user->createToken('user')->plainTextToken;
        $cookie = cookie('jwt', $token, 60 * 24); // Cookie para armazenar o JWT

        return [
            'success' => true,
            'user' => $user,
            'token' => $token,
            'cookie' => $cookie
        ];
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete(); 
    }
}