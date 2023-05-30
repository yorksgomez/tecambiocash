<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'currency_value_id',
        'identificator'
    ];

    public function user() : HasOne {
        return $this->hasOne(User::class);
    }

    public function currency() : HasOne {
        return $this->hasOne(CurrencyValue::class);
    }

}
