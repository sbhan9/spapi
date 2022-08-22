<?php

use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->controller(AdminController::class)->group(function () {
    Route::get('/students', 'students');
    Route::post('/add_siswa', 'add_siswa');
    Route::post('/update_siswa', 'update_siswa');
    Route::post('/delete_siswa', 'delete_siswa');
});