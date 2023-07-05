<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role == "ADMIN";
    }

    /**
     * Determine whether the user can create models.
     */
    public function createCashier(User $user): bool
    {
        return $user->role == "ADMIN";
    }

    /**
     * Determine whether the user can update the model.
     */
    public function enable(User $user): bool
    {
        return $user->role == "ADMIN";
    }

    public function viewUserDoc(User $user): bool
    {
        return $user->role == "ADMIN";
    }

    public function viewUserImage(User $user): bool
    {
        return $user->role == "ADMIN";
    }

    public function changePrestacash(User $user): bool
    {
        return $user->role == "ADMIN";
    }

    public function changeBalance(User $user): bool
    {
        return $user->role == "ADMIN";
    }

    public function findByMail(User $user): bool
    {
        return $user->role == "ADMIN";
    }

    public function addPrestacash(User $user): bool
    {
        return $user->role == "CLIENTE" || $user->role == "CAJERO";
    }
    
}
