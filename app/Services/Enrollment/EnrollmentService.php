<?php

namespace App\Services\Enrollment;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;

class EnrollmentService
{
    public function store(User $user, Course $course): Enrollment
    {
        $inscripcion = new Enrollment();
        $inscripcion->user_id = $user->id;
        $inscripcion->course_id = $course->id;
        $inscripcion->enrolled_at = now();
        $inscripcion->save();

        return $inscripcion;
    }

    public function destroy(User $user, Course $course): void
    {
        $user->enrollments()
            ->where('course_id', $course->id)
            ->delete();
    }

    public function isEnrolled(User $user, Course $course): bool
    {
        return $user->enrollments()
            ->where('course_id', $course->id)
            ->exists();
    }
}