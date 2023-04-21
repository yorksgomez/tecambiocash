<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Models\CurrencyValue;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CurrencyValueController extends BaseController
{
    
    public function showAll() {
        return CurrencyValue::get('name');
    }

    public function select(String $name) {
        return CurrencyValue::where('name', $name)->first();
    }
    
    public function update(String $name, Request $request) {
        $data = $request->all();
        $currency = CurrencyValue::where('name', $name)->first();

        $validator = Validator::make($data, [
            'name' => 'prohibited',
            'id' => 'prohibited',
            'created_at' => 'prohibited',
            'updated_at' => 'prohibited',
            'value' => 'required'
        ]);

        if($validator->fails())
            return $this->sendError('Error de validación', $validator->errors(), 400);

        $currency->update($data);
        return $this->sendResponse("Correcto", "Actualización hecha correctamente");
    }

    public function convert(String $name1, String $name2, Float $amount) {
        $currency1 = CurrencyValue::where('name', $name1)->first();
        $currency2 = CurrencyValue::where('name', $name2)->first();

        $converted = $currency1->value * $amount / $currency2->value;
        return $this->sendResponse($converted, "Conversión hecha correctamente");
    }

}
