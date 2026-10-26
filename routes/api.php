<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Category\CategoryController;
use App\Http\Controllers\Api\V1\Content\ContentController;
use App\Http\Controllers\Api\V1\Course\CourseController;
use App\Http\Controllers\Api\V1\Instructor\InstructorController;
use App\Http\Controllers\Api\V1\Module\ModuleController;
use App\Http\Controllers\Api\V1\Enrollment\EnrollmentController;


Route::pattern('content', '[0-9]+');
Route::pattern('course', '[0-9]+');
Route::pattern('instructor', '[0-9]+');
Route::pattern('module', '[0-9]+');

/*
 * RUTAS PUBLICAS
 */
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/categories', [CategoryController::class, 'index']);

Route::get('/courses', [CourseController::class, 'catalog']);
Route::get('/courses/{course}', [CourseController::class, 'show']);

Route::get('/instructors/{instructor}', [InstructorController::class, 'show']);

/*
 * RUTAS QUE EXIGEN TOKEN
 */
Route::middleware('auth:sanctum')->group(function () {

    // Sesion
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Perfil de instructor
    Route::post('/instructors', [InstructorController::class, 'store']);
    Route::get('/instructors/me', [InstructorController::class, 'me']);
    Route::patch('/instructors/me', [InstructorController::class, 'update']);
    Route::post('/instructors/me/photo', [InstructorController::class, 'updatePhoto']);

    // Cursos
    Route::get('/courses/me', [CourseController::class, 'index']);
    Route::post('/courses', [CourseController::class, 'store']);
    Route::patch('/courses/{course}', [CourseController::class, 'update']);
    Route::delete('/courses/{course}', [CourseController::class, 'destroy']);

    // Modulos
    Route::post('/courses/{course}/modules', [ModuleController::class, 'store']);
    Route::patch('/courses/{course}/modules/order', [ModuleController::class, 'reorder']);
    Route::patch('/modules/{module}', [ModuleController::class, 'update']);
    Route::delete('/modules/{module}', [ModuleController::class, 'destroy']);

    // Contenidos
    Route::post('/modules/{module}/contents', [ContentController::class, 'store']);
    Route::patch('/modules/{module}/contents/order', [ContentController::class, 'reorder']);
    Route::patch('/contents/{content}', [ContentController::class, 'update']);
    Route::delete('/contents/{content}', [ContentController::class, 'destroy']);



    // Inscripciones
    Route::post('/courses/{course}/enrollment', [EnrollmentController::class, 'store']);
    Route::delete('/courses/{course}/enrollment', [EnrollmentController::class, 'destroy']);
});