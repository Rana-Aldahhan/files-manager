<?php

namespace App\Http\Controllers;

use App\Interfaces\UserRepositoryInterface;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function __construct(private AuthService $authService)
    {
    }

    private function checkValidationError($validator)
    {
        $validator?->fails() ?
            throw ValidationException::withMessages([
                $validator?->errors()->first()
            ]) : null;
    }

    public function register(Request $request)
    {
        //TODO should validations be in a middleware?
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
        ]);
        //case of input validation failure
        $this->checkValidationError($validator);
        $user = $this->authService->register($request->name, $request->email, $request->password);
        return $this->successResponse($user, 201);
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        //case of input validation failure
        $this->checkValidationError($validator);
        $user = $this->authService->login($request->email, $request->password);
        return $this->successResponse($user);
    }
    public function logout()
    {
        $this->authService->logout();
        return $this->successResponse(['message' => 'logged out successfully']);
    }
}
