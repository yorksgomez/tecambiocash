<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ApplicationPolicy
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
    public function create(User $user): bool
    {
        return $user->role == "CAJERO" || $user->role == "CLIENTE";
    }

    /**
     * Determine whether user can accept or decline application
     */
    public function judge(User $user): bool
    {
        return $user->role == "ADMIN";
    }
}
