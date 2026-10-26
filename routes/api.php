<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Category\CategoryController;
use App\Http\Controllers\Api\V1\Course\CourseController;
use App\Http\Controllers\Api\V1\Module\ModuleController;
use App\Http\Controllers\Api\V1\Content\ContentController;
use App\Http\Controllers\Api\V1\Instructor\InstructorController;






Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/courses', [CourseController::class, 'catalog']);
Route::get('/courses/{course}', [CourseController::class, 'show'])->whereNumber('course');
Route::get('/instructors/{instructor}', [InstructorController::class, 'show'])->whereNumber('instructor');



Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/courses', [CourseController::class, 'store']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/courses/me', [CourseController::class, 'index']);
    Route::patch('/courses/{course}', [CourseController::class, 'update'])->whereNumber('course');
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->whereNumber('course');
    Route::post('/instructors', [InstructorController::class, 'store']);
    Route::post('/instructors/me/photo', [InstructorController::class, 'updatePhoto']);
    Route::post('/courses/{course}/modules', [ModuleController::class, 'store'])->whereNumber('course');
    Route::patch('/modules/{module}', [ModuleController::class, 'update'])->whereNumber('module');
    Route::delete('/modules/{module}', [ModuleController::class, 'destroy'])->whereNumber('module');
    Route::post('/modules/{module}/contents', [ContentController::class, 'store'])->whereNumber('module');
    Route::patch('/contents/{content}', [ContentController::class, 'update'])->whereNumber('content');
    Route::delete('/contents/{content}', [ContentController::class, 'destroy'])->whereNumber('content');
    Route::patch('/courses/{course}/modules/order', [ModuleController::class, 'reorder'])->whereNumber('course');
    Route::patch('/modules/{module}/contents/order', [ContentController::class, 'reorder'])->whereNumber('module');
    Route::patch('/instructors/me', [InstructorController::class, 'update']);
    Route::get('/instructors/me', [InstructorController::class, 'me']);
});