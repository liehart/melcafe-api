<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\MenuController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\DistanceController;
use App\Http\Controllers\API\OrderStatusController;
use App\Http\Controllers\API\ReceiptController;
use App\Http\Controllers\API\VerificationController;
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

Route::prefix('auth')->group(function () {
    Route::post('/', [AuthController::class, 'index'])->middleware('auth:api');
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('verify', [VerificationController::class, 'verify']);
});

Route::resource('order', OrderController::class)->middleware('auth:api');
Route::resource('menu', MenuController::class);
Route::post('/order/distance', [DistanceController::class, 'index'])->middleware('auth:api');
Route::post('/order/send_update', [OrderStatusController::class, 'store']);
