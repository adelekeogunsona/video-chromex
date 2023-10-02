<?php

use App\Http\Controllers\VideoController;
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

Route::get('videos', [VideoController::class, 'index']);
Route::get('/video/{id}', [VideoController::class, 'show']);
Route::post('video/stream', [VideoController::class, 'stream']);
Route::get('video/end-stream/{title}', [VideoController::class, 'stop']);
// Route::delete('/{id}', [VideoController::class, 'destroy']);
