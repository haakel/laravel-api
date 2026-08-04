<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Traits\ApiResponse;
use App\Services\AuthService;

/**
 * کنترلر احراز هویت — مدیریت ثبت‌نام، ورود، تمدید و خروج کاربران
 *
 * از JWT (JSON Web Token) برای احراز هویت استفاده می‌شود.
 * تمام متدها توسط AuthService اجرا می‌شوند (الگوی Service Layer).
 */
class AuthController extends Controller
{
    use ApiResponse;

    /**
     * سازنده کنترلر
     *
     * @param AuthService $service  سرویس احراز هویت
     */
    public function __construct(protected AuthService $service) {}

    /**
     * ورود کاربر و دریافت توکن JWT
     *
     * @param LoginRequest $request  درخواست حاوی email و password
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $result = $this->service->login($request->validated());

        if (!$result) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        return $this->successResponse($result, 'Login successful');
    }

    /**
     * ثبت‌نام کاربر جدید
     *
     * @param RegisterRequest $request  درخواست حاوی name, email, password, password_confirmation
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        $result = $this->service->register($request->validated());

        return $this->successResponse($result, 'Registration successful', 201);
    }

    /**
     * تمدید توکن JWT قبل از منقضی شدن
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $result = $this->service->refresh();

        if (!$result) {
            return $this->errorResponse('Token could not be refreshed', 401);
        }

        return $this->successResponse($result, 'Token refreshed');
    }

    /**
     * خروج کاربر و باطل کردن توکن JWT
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->service->logout();

        return $this->successResponse(null, 'Successfully logged out');
    }
}
