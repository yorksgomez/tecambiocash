<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Models\Customer;
use App\Models\User;
use Faker\Core\File;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
            'role' => 'prohibited',
            'role_id' => 'prohibited',
            'country' => 'required',
            'nit_type' => 'required',
            'nit' => 'required',
            'phone' => 'required',
            'doc_image' => 'required',
            'customer_image' => 'required',
            'state' => 'prohibited'
        ]);

        if($validator->fails())
            return $this->sendError('Error de validación', $validator->errors(), 400);

        $data['role'] = 'CLIENTE';
        $data['password'] = Hash::make($data['password']);
        $data['state'] = 'INACTIVE';
        $accepted_extensions = ['png', 'jpg', 'jpeg'];

        $filename = time();
        $doc_image = $request->file('doc_image');
        $customer_image = $request->file('customer_image');

        if(!in_array($doc_image->extension(), $accepted_extensions) || !in_array($customer_image->extension(), $accepted_extensions)) 
            return $this->sendError("FILE_EXTENSION_NOT_VALID", [], 403);

        $doc_image_name = "$filename-doc." . $doc_image->extension(); 
        $profile_name = "$filename-profile." . $customer_image->extension();
        Storage::putFileAs("", $doc_image, $doc_image_name);
        Storage::putFileAs("", $customer_image, $profile_name);

        $data['doc_image'] = $doc_image_name;
        $data['customer_image'] = $profile_name;

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
            'state' => 'prohibited'
        ]);

        if($validator->fails())
            return $this->sendError('Error de validación', $validator->errors(), 400);

        $data['role'] = 'CAJERO';
        $data['role_id'] = 0;
        $data['password'] = Hash::make($data['password']);
        $data['state'] = 'INACTIVE';

        User::create($data);
        return $this->sendResponse("Usuario creado", "Usuario creado correctamente");
    }

    public function showCashiers(Request $request) {
        return User::where('role', 'CAJERO')->get();
    }

    public function showCustomers(Request $request) {
        return User::where('role', 'CLIENTE ')->get();
    }

    public function login(Request $request) {
        
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password, 'state' => 'ACTIVE'])) {
            $user = auth()->user();
            
            $success['token'] = $user->createToken('cliente')->plainTextToken;
            $success['user'] = $user;
        
            return $this->sendResponse($success, 'Usuario logueado correctamente');
        } else {
            return $this->sendError('No autorizado', ['error' => 'No autorizado']);
        }

    }

    public function enableUser(int $id) {
        $user = User::find($id);
        $user->state = "ACTIVE";
        $user->save();
        $this->sendResponse("OK", "");
    }
    
    public function showUserDocImage(int $id) {
        $image = Customer::find(User::find($id)->role_id)->doc_image;

        return response(
            Storage::get($image),
            200,
            [
                "Content-Type" => Storage::mimeType($image)
            ]
        );
    }

    public function showUserImage(int $id) {
        $image = Customer::find(User::find($id)->role_id)->customer_image;

        return response(
            Storage::get($image),
            200,
            [
                "Content-Type" => Storage::mimeType($image)
            ]
        );
    }

}
