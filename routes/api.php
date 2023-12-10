<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\PassGeneratorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GooglePassGeneratorController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/generate-pass', [PassGeneratorController::class, 'generatePass']);
Route::post('/google-generate-pass', [GooglePassGeneratorController::class, 'generatePass']);


Route::post('/login', [LoginController::class,'login']);
Route::post('/register', [LoginController::class,'register']);

Route::middleware('auth:api')->group(function (){
    Route::get('/get-user', [LoginController::class,'getUser']);
});
//Route::post('/password/reset', [LoginController::class,'passwordReset']);
Route::post('/forget-reset', [LoginController::class,'forgetReset']);
