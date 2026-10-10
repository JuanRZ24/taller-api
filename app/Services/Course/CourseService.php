<?php

namespace App\Services\Course;

use Illuminate\Support\Facades\Storage;
use App\Models\Course;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Enums\CourseStatus;


class CourseService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }


    public function store(User $user, array $data): Course
    {
        return $user->courses()->create($data);
    }


    public function paginateByInstructor(User $instructor, int $perPage = 10): LengthAwarePaginator
    {
        return $instructor->courses()->with('category')->latest()->paginate($perPage);
    }

    public function paginatePublished(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return Course::query()
            ->where('status', CourseStatus::Published)
            ->with(['category', 'user'])
            ->when($filters['category_id'] ?? null,
                fn ($q, $id) => $q->where('category_id', $id))
            ->when($filters['level'] ?? null,
                fn ($q, $level) => $q->where('level', $level))
            ->when($filters['search'] ?? null,
                fn ($q, $search) => $q->where('title', 'ilike', "%{$search}%"))
            ->latest()
            ->paginate($perPage);
    }

    public function update(Course $course, array $data): Course
    {
        $course->update($data);

        return $course;
    }

    public function destroy(Course $course): void
    {
        $coverPath = $course->cover_path;

        $course->delete();

        if ($coverPath) {
            Storage::disk('public')->delete($coverPath);
        }
    }
}
