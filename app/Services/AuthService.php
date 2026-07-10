<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function login(array $credentials): ?array
    {
        if (!$token = auth('api')->attempt($credentials)) {
            return null;
        }

        return $this->formatTokenResponse($token);
    }

    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = auth('api')->login($user);

        return $this->formatTokenResponse($token);
    }

    public function refresh(): ?array
    {
        try {
            $token = auth('api')->refresh();
            return $this->formatTokenResponse($token);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function logout(): bool
    {
        try {
            auth('api')->logout();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function formatTokenResponse(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ];
    }
}
