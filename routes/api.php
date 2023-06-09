<?php

use App\Http\Controllers\API\ApplicationController;
use App\Http\Controllers\API\BankAccountController;
use App\Http\Controllers\API\ConfigurationController;
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
    Route::get('user/email/{email}', 'findByMail');
});

Route::controller(CurrencyValueController::class)->group(function() {
    Route::get('currency_value', 'showAll');
    Route::get('currency_value/{name}', 'select');
    Route::get('currency_value/{name1}/{name2}/{amount}', 'convert');
});

//AUTH REQUIRED ROUTES
Route::middleware('auth:sanctum')->controller(UserController::class)->group(function() {
    Route::post('user/cashier/create', 'createCashier');
    Route::get('user/current', 'current');
    Route::get('user/cashier', 'showCashiers');
    Route::get('user/customer', 'showCustomers');
    Route::put('user/{id}/enable', 'enableUser');
    Route::put('user/prestacash/add/{amount}', 'addPrestacash');
    Route::put('user/{user_id}/prestacash/{prestacash}', 'changePrestacash');
    Route::put('user/{user_id}/balance/{balance}', 'changeBalance');
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
    Route::get('transaction/process', 'showProcess');
    Route::put('transaction/{transaction_id}/take', 'take');
    Route::put('transaction/{transaction_id}/complete', 'complete');
    Route::get('transaction/{id}/voucher-user', 'showVoucherUserImage');
    Route::post('transaction/{transaction_id}/voucher-user', 'addVoucherUser');
    Route::get('transaction/{id}/voucher-cashier', 'showVoucherCashierImage');
    Route::post('transaction/{transaction_id}/voucher-cashier', 'addVoucherCashier');
});

Route::middleware('auth:sanctum')->controller(BankAccountController::class)->group(function() {
    Route::post('account', 'create');
    Route::get('account', 'showAll');
    Route::put('account/{id}', 'update');
    Route::delete('account/{id}', 'remove');
    Route::get('account/email/{email}', 'findByUserMail');
    Route::get('account/user/{user_id}', 'showUserBanks');
});

Route::middleware('auth:sanctum')->controller(ApplicationController::class)->group(function() {
    Route::post('application', 'create');
    Route::get('application', 'showAll');
    Route::put('application/{id}/accept', 'accept');
    Route::put('application/{id}/decline', 'decline');
});

Route::middleware('auth:sanctum')->controller(ConfigurationController::class)->group(function() {
    Route::get('config', 'show');
    Route::put('config', 'update');
});
