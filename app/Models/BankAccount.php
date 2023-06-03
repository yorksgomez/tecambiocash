<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'currency_value_id',
        'identificator'
    ];

    public function user() : BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function currency() : BelongsTo {
        return $this->belongsTo(CurrencyValue::class);
    }

}
