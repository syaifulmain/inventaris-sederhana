<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Exception;

class AuthService
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function login(array $credentials)
    {
        if (!Auth::attempt($credentials)) {
            throw new Exception('Email atau password salah');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            throw new Exception('Akun Anda tidak aktif');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ];
    }

    public function logout()
    {
        $user = Auth::user();
        $user->tokens()->delete();
        return true;
    }

    public function updateProfile($userId, array $data)
    {
        $user = $this->user->findOrFail($userId);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return $user->fresh();
    }
}

