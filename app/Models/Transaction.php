<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_from',
        'currency_from_id',
        'currency_to_id',
        'user_taker',
        'type',
        'status',
        'total',
        'comission',
        'amount',
        'voucher'
    ];

    public function userFrom() {
        return $this->hasOne(User::class, 'id', 'user_from');
    }

    public function currency() {
        return $this->hasOne(CurrencyValue::class, 'id', 'currency_from_id');
    }

    public function currencyTo() {
        return $this->hasOne(CurrencyValue::class, 'id', 'currency_to_id');
    }

    public function userTaker() {
        return $this->hasOne(User::class, 'id', 'user_taker');
    }

}
