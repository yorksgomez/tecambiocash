<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Models\Application;
use App\Models\BankAccount;
use App\Models\CurrencyValue;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use BackedEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApplicationController extends BaseController
{
    
    public function create(Request $request) {
        $data = $request->all();
        $user = auth()->user();

        $validator = Validator::make($data, [
            "name1" => 'required',
            "lastname1" => 'required',
            "phone1" => 'required',
            "email1" => 'required',
            "name1" => 'required',
            "relationship1" => 'required',
            "name2" => 'required',
            "lastname2" => 'required',
            "phone2" => 'required',
            "email2" => 'required',
            "name2" => 'required',
            "relationship2" => 'required',
        ]);

        if($validator->fails())
            return $this->sendError('Error de validación', $validator->errors(), 400);

        $data['user_id'] = $user->id;

        Application::create($data);
        return $this->sendResponse("OK", "OK");
    }

    public function showAll() {
        $user = auth()->user();
        $applications = null;

        if($user->role == 'ADMIN')
            $applications = Application::with(['user'])->where('status', 'ESPERA')->get();

        return $this->sendResponse($applications, "OK");
    }
    
    public function accept(int $id) {
        $application = Application::find($id);
        $application->state = "APROVADO";
        $application->save();
        return $this->sendResponse("OK", "OK");
    }

    public function decline(int $id) {
        $application = Application::find($id);
        $application->state = "APROVADO";
        $application->save();
        return $this->sendResponse("OK", "OK");
    }

}
