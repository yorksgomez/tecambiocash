<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController
{
    
    public function create(Request $request) {
        $data = $request->all();

        $validator = Validator::make($data, [
            'name' => 'required|string',
            'email' => 'required|email',
            'email_verified_at' => 'prohibited',
            'password' => 'required',
            'c_password' => 'required|same:password',
            'role' => 'prohibited',
            'role_id' => 'prohibited',
            'country' => 'required',
            'nit_type' => 'required',
            'nit' => 'required',
            'phone' => 'required',
            'doc_image' => 'required',
            'customer_image' => 'required',
        ]);

        if($validator->fails())
            return $this->sendError('Error de validación', $validator->errors(), 400);

        $data['role'] = 'CLIENTE';
        $data['password'] = Hash::make($data['password']);

        $customer = Customer::make($data);
        $customer->save();
        $data['role_id'] = $customer->id;

        User::create($data);
        return $this->sendResponse("Usuario creado", "Usuario creado correctamente");
    }

    public function createCashier(Request $request) {
        $data = $request->all();

        $validator = Validator::make($data, [
            'name' => 'required|string',
            'email' => 'required|email',
            'email_verified_at' => 'prohibited',
            'password' => 'required',
            'role' => 'prohibited',
            'role_id' => 'prohibited',
            'phone' => 'required',
        ]);

        if($validator->fails())
            return $this->sendError('Error de validación', $validator->errors(), 400);

        $data['role'] = 'CAJERO';
        $data['role_id'] = 0;
        $data['password'] = Hash::make($data['password']);

        User::create($data);
        return $this->sendResponse("Usuario creado", "Usuario creado correctamente");
    }

    public function showCashiers(Request $request) {
        return User::where('role', 'CAJERO')->get();
    }

    public function login(Request $request) {
        
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = auth()->user();
            
            $success['token'] = $user->createToken('cliente')->plainTextToken;
            $success['user'] = $user;
        
            return $this->sendResponse($success, 'Usuario logueado correctamente');
        } else {
            return $this->sendError('No autorizado', ['error' => 'No autorizado']);
        }

    }

}
