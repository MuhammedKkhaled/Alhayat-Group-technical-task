<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    public function attemptSession(array $credentials): bool
    {
        return Auth::guard('web')->attempt($credentials);
    }

    public function issueApiToken(string $email, string $password): ?string
    {
        $user = $this->users->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        return $user->createToken('api')->plainTextToken;
    }
}
