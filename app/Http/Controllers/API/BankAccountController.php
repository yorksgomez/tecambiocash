<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Models\BankAccount;
use App\Models\CurrencyValue;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use BackedEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BankAccountController extends BaseController
{
    
    public function create(Request $request) {
        $this->authorize('create', BankAccount::class);
        $data = $request->all();
        $user = auth()->user();

        $validator = Validator::make($data, [
            'user_id' => 'required',
            'currency' => 'required',
            'identificator' => 'required'
        ]);

        $data['currency_value_id'] = CurrencyValue::where('name', $data['currency'])->first()->id;

        if($validator->fails())
            return $this->sendError('Error de validación', $validator->errors(), 400);

        BankAccount::create($data);
        return $this->sendResponse("OK", "OK");
    }

    public function showAll() {
        $this->authorize('viewAny', BankAccount::class);
        $user = auth()->user();
        $accounts = null;

        if($user->role == 'CLIENTE')
            $accounts = BankAccount::with(['currency'])->where('user_id', $user->id)->get();
        elseif($user->role == 'CAJERO')
            $accounts = BankAccount::with(['currency'])->where('user_id', $user->id)->get();
        elseif($user->role == 'ADMIN')
            $accounts = [];

        return $this->sendResponse($accounts, "OK");
    }
    
    public function update(Request $request, int $id) {
        $account = BankAccount::find($id);

        $this->authorize('update', $account);

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

        
        $account->update($data);
        return $this->sendResponse("OK", "OK");
    }

    public function createFromArrays(Request $request, User $user) {
        
        for($i = 0; $i < count($request->account_name); $i++) {
            $account_id = CurrencyValue::where('name', $request->account_name[$i])->first()->id;
            $identificator = $request->identificator[$i];
            
            BankAccount::create([
                'user_id' => $user->id,
                'currency_value_id' => $account_id,
                'identificator' => $identificator
            ]);
        }

    }

    public function remove(int $id) {
        $account = BankAccount::find($id);
        $this->authorize('delete', BankAccount::class);
        $account->delete();
        return $this->sendResponse("OK", "OK");
    }

    public function findByUserMail($email) {
        $this->authorize('viewByMail', BankAccount::class);
        $id = User::where('email', $email)->first()->id;
        $accounts = BankAccount::with(['currency'])->where('user_id', $id)->get();
        return $this->sendResponse($accounts, "OK");
    }

    public function showUserBanks(int $user_id) {
        $user = User::find($user_id);
        $this->authorize('viewUserBanks', [BankAccount::class, $user]);
        return $this->sendResponse(BankAccount::with(['currency'])->where('user_id', $user->id)->get(), "OK");
    }

}
