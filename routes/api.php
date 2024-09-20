<?php

use App\Http\Controllers\passportController;
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


Route::post('register', [passportController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('verify', [passportController::class, 'verifyCodeOnly']);
Route::post('user/forgetpass',[passportController::class,'userforgetpassword']);
Route::post('user/checkpass',[passportController::class,'usercheckpassword']);
Route::post('user/reset',[passportController::class,'userResetpassword']);
Route::middleware('auth:api', 'verified')->group(function () {
    Route::get('user', function (Request $request) {
        return $request->user();
    });
});

