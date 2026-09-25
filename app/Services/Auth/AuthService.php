<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;

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


}
