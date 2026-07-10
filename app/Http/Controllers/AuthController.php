<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Traits\ApiResponse;
use App\Services\AuthService;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(protected AuthService $service) {}

    public function login(LoginRequest $request)
    {
        $result = $this->service->login($request->validated());

        if (!$result) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        return $this->successResponse($result, 'Login successful');
    }

    public function register(RegisterRequest $request)
    {
        $result = $this->service->register($request->validated());

        return $this->successResponse($result, 'Registration successful', 201);
    }

    public function refresh()
    {
        $result = $this->service->refresh();

        if (!$result) {
            return $this->errorResponse('Token could not be refreshed', 401);
        }

        return $this->successResponse($result, 'Token refreshed');
    }

    public function logout()
    {
        $this->service->logout();

        return $this->successResponse(null, 'Successfully logged out');
    }
}
