<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * سرویس احراز هویت — مدیریت JWT Token
 *
 * از بسته tymon/jwt-auth برای صدور، تمدید و باطل کردن توکن‌ها استفاده می‌شود.
 */
class AuthService
{
    /**
     * ورود کاربر با ایمیل و رمز عبور
     *
     * @param array $credentials  آرایه حاوی 'email' و 'password'
     * @return array|null         آرایه توکن در صورت موفقیت، null در صورت شکست
     */
    public function login(array $credentials): ?array
    {
        if (!$token = auth('api')->attempt($credentials)) {
            return null;
        }

        return $this->formatTokenResponse($token);
    }

    /**
     * ثبت‌نام کاربر جدید + صدور توکن
     *
     * @param array $data  آرایه حاوی 'name', 'email', 'password'
     * @return array       آرایه توکن JWT
     */
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

    /**
     * تمدید توکن JWT
     *
     * @return array|null  آرایه توکن جدید یا null در صورت شکست
     */
    public function refresh(): ?array
    {
        try {
            $token = auth('api')->refresh();
            return $this->formatTokenResponse($token);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * باطل کردن توکن فعلی کاربر
     *
     * @return bool  true در صورت موفقیت
     */
    public function logout(): bool
    {
        try {
            auth('api')->logout();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * تبدیل توکن خام به فرمت استاندارد پاسخ API
     *
     * @param string $token  توکن JWT خام
     * @return array         آرایه حاوی access_token, token_type, expires_in
     */
    protected function formatTokenResponse(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ];
    }
}
