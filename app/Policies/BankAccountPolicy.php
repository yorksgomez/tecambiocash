<?php

namespace App\Policies;

use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BankAccountPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role == "ADMIN";
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BankAccount $bankAccount): bool
    {
        return $user->role == "ADMIN";
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BankAccount $bankAccount): bool
    {
        return $user->role == "ADMIN";
    }

    public function viewUserBanks(User $user, User $user_watch): bool {
        return $user->role == "ADMIN" ||
               (Transaction::where([['user_from', $user->id],['user_taker', $user_watch->id],['status', '!=', 'COMPLETADA']])->count() > 0) ||
               (Transaction::where([['user_taker', $user->id],['user_from', $user_watch->id],['status', '!=', 'COMPLETADA']])->count() > 0);
    }

    public function viewByMail(User $user) {
        return $user->role == "ADMIN";
    }

}
