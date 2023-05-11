<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_from',
        'currency_id',
        'type',
        'status',
        'amount',
        'voucher'
    ];

    public function userFrom() {
        return $this->hasOne(User::class, 'id', 'user_from');
    }

    public function currency() {
        return $this->hasOne(CurrencyValue::class, 'id', 'currency');
    }

    public function userTaker() {
        return $this->hasOne(User::class, 'id', 'user_taker');
    }

}
