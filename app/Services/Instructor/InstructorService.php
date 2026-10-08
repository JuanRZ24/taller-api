<?php

namespace App\Services\Instructor;

use App\Enums\UserRole;
use App\Models\InstructorProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InstructorService
{
    public function store(User $user, array $data): InstructorProfile
    {
        return DB::transaction(function () use ($user, $data) {
            $profile = $user->instructorProfile()->create($data);

            $user->role = UserRole::Instructor;
            $user->save();

            return $profile;
        });
    }
}