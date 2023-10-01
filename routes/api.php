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

Route::prefix('video')->group(function () {
    Route::get('/all', [VideoController::class, 'index'])->name('videos.index');
    Route::post('/store', [VideoController::class, 'store'])->name('videos.store');
    Route::get('/{video}', [VideoController::class, 'show'])->name('videos.show');
    Route::delete('/{video}', [VideoController::class, 'destroy'])->name('videos.destroy');
});
