<?php

namespace App\Policies;

use App\Enums\CourseStatus;
use App\Enums\UserRole;
use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function create(User $user): bool
    {
        return $user->role === UserRole::Instructor;
    }

    public function view(User $user, Course $course): bool
    {
        return $course->status === CourseStatus::Published
            || $this->esDueno($user, $course);
    }

    public function update(User $user, Course $course): bool
    {
        return $this->esDueno($user, $course)
            && in_array($course->status, [CourseStatus::Draft, CourseStatus::Rejected], true);
    }

    public function delete(User $user, Course $course): bool
    {
        return $this->update($user, $course);
    }

    private function esDueno(User $user, Course $course): bool
    {
        return $user->id === $course->user_id;
    }
}