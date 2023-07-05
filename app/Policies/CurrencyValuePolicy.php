<?php

namespace App\Policies;

use App\Models\CurrencyValue;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CurrencyValuePolicy
{

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->role == "ADMIN";
    }

}
