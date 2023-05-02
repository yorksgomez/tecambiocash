<?php

use App\Http\Controllers\API\CurrencyValueController;
use App\Http\Controllers\API\UserController;
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

Route::controller(UserController::class)->group(function() {
    Route::post('user/create', 'create');
    Route::post('user/cashier/create', 'createCashier');
    Route::get('user/cashier', 'showCashiers');
    Route::get('user/customer', 'showCustomers');
    Route::post('user/login', 'login');
    Route::put('user/{id}/enable', 'enableUser');
    Route::get('user/{id}/doc-image', 'showUserDocImage');
    Route::get('user/{id}/image', 'showUserImage');
});

Route::controller(CurrencyValueController::class)->group(function() {
    Route::get('currency_value', 'showAll');
    Route::get('currency_value/{name}', 'select');
    Route::put('currency_value/{name}', 'update');
    Route::get('currency_value/{name1}/{name2}/{amount}', 'convert');
});