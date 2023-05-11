<?php

use App\Http\Controllers\API\CurrencyValueController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\UserController;
use Brick\Math\Exception\RoundingNecessaryException;
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

//NO AUTH ROUTES
Route::controller(UserController::class)->group(function() {
    Route::post('user/create', 'create');
    Route::post('user/login', 'login');
});

Route::controller(CurrencyValueController::class)->group(function() {
    Route::get('currency_value', 'showAll');
    Route::get('currency_value/{name}', 'select');
    Route::get('currency_value/{name1}/{name2}/{amount}', 'convert');
});

//AUTH REQUIRED ROUTES
Route::middleware('auth:sanctum')->controller(UserController::class)->group(function() {
    Route::post('user/cashier/create', 'createCashier');
    Route::get('user/cashier', 'showCashiers');
    Route::get('user/customer', 'showCustomers');
    Route::put('user/{id}/enable', 'enableUser');
    Route::get('user/{id}/doc-image', 'showUserDocImage');
    Route::get('user/{id}/image', 'showUserImage');
});

Route::middleware('auth:sanctum')->controller(CurrencyValueController::class)->group(function() {
    Route::put('currency_value/{name}', 'update');
});

Route::middleware('auth:sanctum')->controller(TransactionController::class)->group(function() {
    Route::post('transaction', 'create');
    Route::get('transaction', 'showAll');
    Route::get('transaction/waiting', 'showWaiting');
    Route::put('transaction/{transaction_id}/take');
    Route::put('transaction/{transaction_id}/complete');
    Route::get('transaction/{id}/voucher', 'showVoucherImage');
});