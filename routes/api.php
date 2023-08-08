<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*Routes Protegés par le middleware*/

Route::middleware('jwt.verify')->group(function () {


    
});

/* Route du controller AuthController avec JWT  */
Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');

    Route::match(['get', 'post'], 'update', 'updateProfile');

    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');

    Route::delete('/delete/', 'destroy');
});