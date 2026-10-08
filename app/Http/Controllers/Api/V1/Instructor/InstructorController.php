<?php

namespace App\Http\Controllers\Api\V1\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\StoreInstructorRequest;
use App\Http\Resources\InstructorProfileResource;
use App\Http\Resources\UserResource;
use App\Services\Instructor\InstructorService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Instructor\UpdateInstructorPhotoRequest;

class InstructorController extends Controller
{
    public function __construct(
        private readonly InstructorService $instructorService,
    ) {}

    public function store(StoreInstructorRequest $request): JsonResponse
    {
        $user = $request->user();

        $profile = $this->instructorService->store($user, $request->validated());

        return response()->json([
            'user' => new UserResource($user),
            'profile' => new InstructorProfileResource($profile),
        ], 201);
    }


        public function updatePhoto(UpdateInstructorPhotoRequest $request): JsonResponse
    {
        $profile = $this->instructorService->updatePhoto(
            $request->user(),
            $request->file('photo'),
        );

        return response()->json([
            'profile' => new InstructorProfileResource($profile),
        ]);
    }
}