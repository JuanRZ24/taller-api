<?php

namespace App\Policies;

use App\Enums\CourseStatus;
use App\Enums\UserRole;
use App\Models\Course;
use App\Models\User;
use App\Services\Enrollment\EnrollmentService;

class CoursePolicy
{
    public function __construct(
        private readonly EnrollmentService $enrollmentService,
    ) {}




    public function create(User $user): bool
    {
        return $user->role === UserRole::Instructor;
    }

   public function view(?User $user, Course $course): bool
{
    return $course->status === CourseStatus::Published
        || ($user && $this->esDueno($user, $course));
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

       public function enroll(User $user, Course $course): bool
    {
        return $course->status === CourseStatus::Published
            && ! $this->esDueno($user, $course);
    }

        public function viewContent(?User $user, Course $course): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->esDueno($user, $course)
            || $this->enrollmentService->isEnrolled($user, $course);
    }

    private function esDueno(User $user, Course $course): bool
    {
        return $user->id === $course->user_id;
    }

     
}