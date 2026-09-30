<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Enums\UserStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\HttpException;


class AuthService
{
    public function register(array $data): array
    {
        return DB::transaction(function() use ($data) {
            $user = new User([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                
            ]);

            $user->role = $data['role'];
            $user->save();

            return [
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken,
            ];
        });

    }



    public function login (array $data): array
{ 
    $user = User::where('email', $data['email'])->first();

    if (!$user || !Hash::check($data['password'], $user->password)){
        throw new HttpException(
            401,
            'Credenciales incorrectas.'
        );
    }

    if ($user->status !== UserStatus::Active){
        throw new HttpException(
            403,
            'Cuenta no activa.'
        );
    }

    return [
        'user' => $user,
        'token' => $user->createToken('auth_token')->plainTextToken,
    ];


}

public function logout (User $user): void 
{
    $token = $user->currentAccessToken();

    if($token){
        $token->delete();
    }
        
}
}