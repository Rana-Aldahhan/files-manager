<?php

namespace App\Services;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthService extends Service
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }


    public function register($name, $email, $password)
    {
        $user = $this->userRepository->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password)
        ]);
        $token = $user->createToken('app')->plainTextToken;
        $user->token = $token;
        return $user;
    }
    public function login($email, $password)
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'credentials do not match with our records'
            ]);
        }
        $token = $user->createToken('app')->plainTextToken;
        $user->token = $token;
        return $user;
    }
    public function logout()
    {
        auth()->user()->tokens()->delete();
        return;
    }
}
