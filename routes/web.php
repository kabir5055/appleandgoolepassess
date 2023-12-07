<?php

use App\Http\Controllers\GooglePassGeneratorController;
use App\Http\Controllers\PassGeneratorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/generate-pass', [PassGeneratorController::class, 'generatePass']);
Route::get('/google-generate-pass', [GooglePassGeneratorController::class, 'generatePass']);
