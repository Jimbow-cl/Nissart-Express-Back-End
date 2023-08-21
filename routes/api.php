<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GareController;
use App\Http\Controllers\VoucherController;
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

/*Routes Protegés par le middleware  Auth.php JWT*/

Route::middleware('jwt.verify')->group(function () {

Route::get('/voucher', [VoucherController::class, 'read']);
Route::post('/voucher/{value}',[VoucherController::class,'create']);
    
});

/*Routes Publiques*/

Route::get('/gares', [GareController::class, 'appelGare']);
Route::get('/gares/{depart}/{arrivee}/{passager}/{date}', [GareController::class, 'calculPrix']);

/* Route du controller AuthController avec création de Token   */
Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');

    Route::match(['get', 'post'], 'update', 'updateProfile');

    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');

    Route::delete('delete', 'destroy');
});