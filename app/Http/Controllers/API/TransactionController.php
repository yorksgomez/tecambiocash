<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Models\CurrencyValue;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TransactionController extends BaseController
{

    public function create(Request $request) {
        $data = $request->all();

        $validator = Validator::make($data, [
            'user_from' => 'prohibited',
            'currency_id' => 'required',
            'type' => 'required',
            'user_taker' => 'prohibited',
            'status' => 'prohibited',
            'amount' => 'required',
            'voucher' => 'required'
        ]);

        if($validator->fails())
            return $this->sendError('Error de validación', $validator->errors(), 400);
    
        $data['user_from'] = auth()->user()->id;
        $data['status'] = 'ESPERA';

        $accepted_extensions = ['png', 'jpg', 'jpeg'];

        $filename = time();
        $voucher = $request->file('voucher');

        if(!in_array($voucher->extension(), $accepted_extensions)) 
            return $this->sendError("FILE_EXTENSION_NOT_VALID", [], 403);

        $voucher_name = "$filename-doc." . $voucher->extension(); 
        Storage::putFileAs("", $voucher, $voucher_name);

        $data['voucher'] = $voucher_name;

        $transaction = Transaction::create($data);
        return $this->sendResponse("OK", "OK");
    }

    public function showAll() {
        $user = auth()->user();
        $transactions = null;

        if($user->role == 'CLIENTE')
            $transactions = Transaction::where('user_from', $user->id)->get();
        elseif($user->role == 'CAJERO')
            $transactions = Transaction::where('user_taker', $user->id)->get();
        elseif($user->role == 'ADMIN')
            $transactions = Transaction::get();

        return $this->sendResponse($transactions, "OK");
    }

    public function showWaiting() {
        $transactions = Transaction::with(['currency'])->where('status', 'ESPERA')->get();
        return $this->sendResponse($transactions, "OK");
    }
        
    public function take(int $transaction_id) {
        $user = auth()->user();
        $transaction = Transaction::find($transaction_id);
    
        $in_progress = Transaction::where('user_taker', $user->id)->where('status', 'EN PROGRESO')->count();

        if($in_progress > 0)
            return $this->sendError("HAS_IN_PROGRESS");

        if($transaction->status == 'ESPERA')
            $transaction->status = 'EN PROGRESO';
        
        $transaction->save();
        return $this->sendResponse("OK", "OK");
    }

    public function complete(int $transaction_id) {
        $user = auth()->user();
        $transaction = Transaction::find($transaction_id);

        if($transaction->status == 'EN PROGRESO')
            $transaction->status = 'COMPLETA';
        
        $transaction->save();
        return $this->sendResponse("OK", "OK");
    }

    public function showVoucherImage(int $id) {
        $image = Transaction::find($id)->voucher;

        return response(
            Storage::get($image),
            200,
            [
                "Content-Type" => Storage::mimeType($image)
            ]
        );
    }

}
