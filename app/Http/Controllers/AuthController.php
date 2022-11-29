<?php

namespace App\Http\Controllers;

use App\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository=$userRepository;
    }

    public function register(Request $request)
    {
        //TODO should validations be in a middleware?
        $validator = Validator::make($request->all(), [
            'name'=>'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
        ]);
        if($validator->fails())//case of input validation failure
        {
            throw ValidationException::withMessages([
                $validator->errors()->first()
            ]);
        }
        $user=$this->userRepository->create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password)
        ]);
        $token=$user->createToken('app')->plainTextToken;
        $user->token=$token;
        return response()->json($user,201);
    }
    public function login(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required',
        ]);
     
        $user = $this->userRepository->findByEmail($request->email);
     
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                $validator->errors()->count()!=0?
                $validator->errors()->first()
                :'credentials do not match with our records'
            ]);
        }
        $token=$user->createToken('app')->plainTextToken;
        $user->token=$token;
        return response()->json($user);
    }
}
