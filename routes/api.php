<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Category\CategoryController;
use App\Http\Controllers\Api\V1\Course\CourseController;
use App\Http\Controllers\Api\V1\Instructor\InstructorController;







Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/courses', [CourseController::class, 'catalog']);
Route::get('/courses/{course}', [CourseController::class, 'show'])->whereNumber('course');


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/courses', [CourseController::class, 'store']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/courses/me', [CourseController::class, 'index']);
    Route::patch('/courses/{course}', [CourseController::class, 'update'])->whereNumber('course');
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->whereNumber('course');
    Route::post('/instructors', [InstructorController::class, 'store']);
    Route::post('/instructors/me/photo', [InstructorController::class, 'updatePhoto']);
   
});