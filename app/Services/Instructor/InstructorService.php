<?php

namespace App\Services\Instructor;

use App\Enums\UserRole;
use App\Models\InstructorProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

        public function updatePhoto(User $user, UploadedFile $photo): InstructorProfile
    {
        $profile = $user->instructorProfile;

        $anterior = $profile->photo_path;

        $profile->photo_path = $photo->store('instructors', 'public');
        $profile->save();

        if ($anterior) {
            Storage::disk('public')->delete($anterior);
        }

        return $profile;
    }


    public function update(User $user, array $data): InstructorProfile
    {
        $profile = $user->instructorProfile;

        $profile->update($data);

        return $profile;
    }
}