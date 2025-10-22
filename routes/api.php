<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CourseController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/students', [StudentController::class, 'store']);
Route::post('/courses', [CourseController::class, 'store']);
Route::post('/courses/{course}/assign', [CourseController::class, 'assign']);
Route::get('/courses/{course}', [CourseController::class, 'show']);
Route::get('/students/{student}', [StudentController::class, 'show']);
