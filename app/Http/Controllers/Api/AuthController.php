<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function store(LoginRequest $request): JsonResponse
    {
        $token = $this->auth->issueApiToken(
            $request->validated('email'),
            $request->validated('password'),
        );

        if (! $token) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        return response()->json(['token' => $token, 'token_type' => 'Bearer']);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(status: 204);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
