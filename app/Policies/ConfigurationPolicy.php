<?php

namespace App\Policies;

use App\Models\Configuration;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ConfigurationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role == "ADMIN";
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Configuration $configuration): bool
    {
        return $user->role == "ADMIN";
    }

}
