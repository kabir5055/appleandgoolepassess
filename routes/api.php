<?php

use App\Http\Controllers\PassGeneratorController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/generate-pass', [PassGeneratorController::class, 'generatePass']);
Route::post('/google-generate-pass', [\App\Http\Controllers\GooglePassGeneratorController::class, 'generatePass']);
