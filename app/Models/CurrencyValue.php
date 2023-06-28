<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrencyValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'value'
    ];

    public static function convert(String $name1, String $name2, Float $amount) {
        $currency1 = CurrencyValue::where('name', $name1)->first();
        $currency2 = CurrencyValue::where('name', $name2)->first();
        $config = Configuration::find(1);

        //IS EXCHANGE
        $comission = $config->comission_exchange;

        //IS WITHDRAW
        if($currency1->id == 1) $comission = $config->comission_out;
        
        //IS ADDITION
        if($currency2->id == 1) $comission = $config->comission_in;

        $converted = $currency1->value * $amount / $currency2->value;
        $comission = $converted * $comission / 100;
        $total = $converted - $comission;
        return ['net' => $converted, 'comission' => $comission, 'total' => $total];
    }

}
