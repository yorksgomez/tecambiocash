<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TransactionPolicy
{

    public function createWithdraw(User $user): bool {
        return $user->role == "CLIENTE" &&
               Transaction::where('user_from', $user->id)->whereNot('status', 'COMPLETADA')->count() <= 0;
    }

    public function createAddition(User $user): bool {
        return $user->role == "CLIENTE" &&
               Transaction::where('user_from', $user->id)->whereNot('status', 'COMPLETADA')->count() <= 0;
    }

    public function createSend(User $user): bool {
        return $user->role == "CLIENTE" || $user->role == "CAJERO";
    }

    public function createExchange(User $user): bool {
        return $user->role == "CLIENTE" &&
               Transaction::where('user_from', $user->id)->whereNot('status', 'COMPLETADA')->count() <= 0;
    }

    public function addVoucherUser(User $user, Transaction $transaction) {
        return $user->role == "CLIENTE" &&
               $transaction->user_from == $user->id;
    }

    public function addVoucherCashier(User $user, Transaction $transaction) {
        return $user->role == "CAJERO" &&
               $transaction->user_taker == $user->id;
    }

    public function viewOwn(User $user) {
        return true;
    }

    public function viewWaiting(User $user) {
        return $user->role == "ADMIN" || $user->role == "CAJERO";
    }

    public function viewProcess(User $user) {
        return $user->role == "ADMIN" || $user->role == "CAJERO";
    }

    public function take(User $user, Transaction $transaction) {
        return $user->role == "CAJERO" &&
               ($transaction->type != "AGREGAR" || $user->total >= $transaction->amount);
    }

    public function completeWithdraw(User $user, Transaction $transaction) {
        return $user->role == "CAJERO" &&
               $transaction->user_taker == $user->id;
    }

    public function completeAddition(User $user, Transaction $transaction) {
        return $user->role == "CAJERO" &&
               $transaction->user_taker == $user->id;
    }

    public function completeExchange(User $user, Transaction $transaction) {
        return $user->role == "CAJERO" &&
               $transaction->user_taker == $user->id;
    }

    public function showVoucher(User $user, Transaction $transaction) {
        return $user->role == "ADMIN" ||
               $transaction->user_from == $user->id ||
               $transaction->user_taker == $user->id;
    }

}
