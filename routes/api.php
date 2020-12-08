<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\DriverController;
use App\Http\Controllers\API\FileUploadController;
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
    Route::get('verify/{token}', [VerificationController::class, 'verify']);
});

Route::resource('order', OrderController::class)->middleware('auth:api');
Route::resource('menu', MenuController::class);
Route::resource('driver', DriverController::class);
Route::post('menu/image/{id}', [MenuController::class, 'updateImage']);
Route::resource('customer', CustomerController::class);
Route::post('/order/distance', [DistanceController::class, 'index']);
Route::post('/order/send_update', [OrderStatusController::class, 'store']);
Route::post('/image', [FileUploadController::class, 'store']);
