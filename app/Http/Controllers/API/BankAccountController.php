<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\User;
use BackedEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BankAccountController extends BaseController
{
    
    public function create(Request $request) {
        $data = $request->all();
        $user = auth()->user();

        $validator = Validator::make($data, [
            'user_id' => 'prohibited',
            'currency_value_id' => 'required',
            'identificator' => 'required'
        ]);

        if($validator->fails())
            return $this->sendError('Error de validación', $validator->errors(), 400);

        $data['user_id'] = $user->id;

        BankAccount::create($data);
        return $this->sendResponse("OK", "OK");
    }

    public function showAll() {
        $user = auth()->user();
        $accounts = null;

        if($user->role == 'CLIENTE')
            $accounts = BankAccount::with(['user', 'currency'])->where('user_id', $user->id)->get();
        elseif($user->role == 'CAJERO')
            $accounts = BankAccount::with(['user', 'currency'])->where('user_id', $user->id)->get();
        elseif($user->role == 'ADMIN')
            $accounts = BankAccount::with(['user', 'currency'])->get();

        return $this->sendResponse($accounts, "OK");
    }
    
    public function update(Request $request, int $id) {
        $data = $request->all();
        $user = auth()->user();

        $validator = Validator::make($data, [
            'user_id' => 'prohibited',
            'currency_value_id' => 'prohibited',
            'identificator' => 'optional'
        ]);

        if($validator->fails())
            return $this->sendError('Error de validación', $validator->errors(), 400);

        $data['user_id'] = $user->id;

        BankAccount::find($id)->update($data);
        return $this->sendResponse("OK", "OK");
    }

}
