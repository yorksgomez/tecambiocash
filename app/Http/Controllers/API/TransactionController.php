<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Models\CurrencyValue;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TransactionController extends BaseController
{

    public function create(Request $request) {
        $type = $request->type;

        switch($type) {
            case 'AGREGAR': return $this->createAddition($request);
            case 'RETIRAR': return $this->createWithdraw($request);
            case 'ENVIAR': return $this->createSend($request);
            case 'INTERCAMBIAR': return $this->createExchange($request);
        }

    }

    public function createWithdraw(Request $request) {
        $this->authorize('createWithdraw', Transaction::class);
        $data = $request->all();
        $user = auth()->user();

        $validator = Validator::make($data, [
            'user_from' => 'prohibited',
            'currency' => 'required',
            'user_taker' => 'prohibited',
            'status' => 'prohibited',
            'amount' => 'required'
        ]);

        if($validator->fails())
            return $this->sendError('Error de validación', $validator->errors(), 400);

        if($user->balance < $data['amount'])
            return $this->sendError('Saldo insuficiente', "Saldo insuficiente");

        $convertion = CurrencyValue::convert("TCS", $data['currency'], $data['amount']);

        $data['comission'] = $convertion['comission'];
        $data['total'] = $convertion['total'];
        $data['currency_from_id'] = CurrencyValue::where('name', $data['currency'])->first()->id;

        $data['user_from'] = auth()->user()->id;
        $data['user_taker'] = null;
        $data['status'] = 'ESPERA';

        

        $transaction = Transaction::create($data);
        return $this->sendResponse("OK", "OK");
    }

    public function createAddition(Request $request) {
        $this->authorize('createAddition', Transaction::class);
        $data = $request->all();

        $validator = Validator::make($data, [
            'user_from' => 'prohibited',
            'currency' => 'required',
            'user_taker' => 'prohibited',
            'status' => 'prohibited',
            'amount' => 'required'
        ]);

        if($validator->fails())
            return $this->sendError('Error de validación', $validator->errors(), 400);
    
        $convertion = CurrencyValue::convert($data['currency'], "TCS", $data['amount']);
        $data['comission'] = $convertion['comission'];
        $data['total'] = $convertion['total'];
        $data['currency_from_id'] = CurrencyValue::where('name', $data['currency'])->first()->id;

        $data['user_from'] = auth()->user()->id;
        $data['user_taker'] = null;
        $data['status'] = 'ESPERA';

        $transaction = Transaction::create($data);
        return $this->sendResponse("OK", "OK");
    }

    public function createSend(Request $request) {
        $this->authorize('createSend', Transaction::class);
        $data = $request->all();
        
        $validator = Validator::make($data, [
            'user_from' => 'prohibited',
            'email' => 'required',
            'status' => 'prohibited',
            'user_taker' => 'prohibited',
            'amount' => 'required'
        ]);

        if($validator->fails())
            return $this->sendError('Error de validación', $validator->errors(), 400);

        $user = User::find(auth()->user()->id);
        $taker = User::where('email', $data['email'])->first();

        if($user->balance < $data['amount'])
            return $this->sendError('Saldo insuficiente', 'Saldo insuficiente', 400);

        $data['currency_from_id'] = 1;

        $data['user_from'] = $user->id;
        $data['user_taker'] = $taker->id;
        $data['status'] = "COMPLETA";

        $transaction = Transaction::create($data);

        $user->balance -= $data['amount'];
        $user->save();
        $taker->balance += $data['amount'];
        $taker->save();
        return $this->sendResponse("OK", "OK");
    }

    public function createExchange(Request $request) {
        $this->authorize('createExchange', Transaction::class);
        $data = $request->all();

        $validator = Validator::make($data, [
            'user_from' => 'prohibited',
            'currency_from' => 'required',
            'currency_to' => 'required',
            'user_taker' => 'prohibited',
            'status' => 'prohibited',
            'amount' => 'required'
        ]);

        if($validator->fails())
            return $this->sendError('Error de validación', $validator->errors(), 400);

        $convertion = CurrencyValue::convert($data['currency_from'], $data['currency_to'], $data['amount']);
        $data['comission'] = $convertion['comission'];
        $data['total'] = $convertion['total'];
        $data['currency_from_id'] = CurrencyValue::where('name', $data['currency_from'])->first()->id;
        $data['currency_to_id'] = CurrencyValue::where('name', $data['currency_to'])->first()->id;

        $data['user_from'] = auth()->user()->id;
        $data['user_taker'] = null;
        $data['status'] = 'ESPERA';

        $transaction = Transaction::create($data);
        return $this->sendResponse("OK", "OK");
    }

    public function addVoucherUser(Request $request, int $transaction_id) {
        $transaction = Transaction::find($transaction_id);
        $this->authorize('addVoucherUser', $transaction);

        $data = $request->all();

        $validator = Validator::make($data, [
            'voucher' => 'required|file'
        ]);

        if($validator->fails())
            return $this->sendError('Error de validación', $validator->errors(), 400);

        
        $accepted_extensions = ['png', 'jpg', 'jpeg'];

        $filename = time();
        $voucher = $request->file('voucher');

        if(!in_array($voucher->extension(), $accepted_extensions)) 
            return $this->sendError("FILE_EXTENSION_NOT_VALID", [], 403);

        $voucher_name = "$filename-doc." . $voucher->extension(); 
        Storage::putFileAs("", $voucher, $voucher_name);

        $transaction->voucher_user = $voucher_name;
        $transaction->status = "PAGADA";
        $transaction->save();
        return $this->sendResponse("OK", "OK");
    }

    public function addVoucherCashier(Request $request, int $transaction_id) {
        $data = $request->all();
        $transaction = Transaction::find($transaction_id);

        $this->authorize('addVoucherCashier', $transaction);

        $validator = Validator::make($data, [
            'voucher' => 'required|file'
        ]);

        if($validator->fails())
            return $this->sendError('Error de validación', $validator->errors(), 400);

        $accepted_extensions = ['png', 'jpg', 'jpeg'];

        $filename = time();
        $voucher = $request->file('voucher');

        if(!in_array($voucher->extension(), $accepted_extensions)) 
            return $this->sendError("FILE_EXTENSION_NOT_VALID", [], 403);

        $voucher_name = "$filename-doc." . $voucher->extension(); 
        Storage::putFileAs("", $voucher, $voucher_name);

        $transaction->voucher_cashier = $voucher_name;
        $transaction->status = "PAGADA";
        $transaction->save();
        return $this->sendResponse("OK", "OK");
    }

    public function showAll() {
        $this->authorize('viewOwn', Transaction::class);
        $user = auth()->user();
        $transactions = null;

        if($user->role == 'CLIENTE')
            $transactions = Transaction::with(['currency'])->where('user_from', $user->id)->get();
        elseif($user->role == 'CAJERO')
            $transactions = Transaction::with(['currency'])->where('user_taker', $user->id)->get();
        elseif($user->role == 'ADMIN')
            $transactions = Transaction::with(['currency'])->get();

        return $this->sendResponse($transactions, "OK");
    }

    public function showWaiting() {
        $this->authorize('viewWaiting', Transaction::class);
        $transactions = Transaction::with(['currency'])->where('status', 'ESPERA')->get();
        return $this->sendResponse($transactions, "OK");
    }
     
    public function showProcess() {
        $this->authorize('viewProcess', Transaction::class);
        $transactions = Transaction::with(['currency'])->where([
            ['status', 'in', ['EN PROGRESO', 'PAGADA']],
            ['user_taker', auth()->user()->id]

        ])->get();
        return $this->sendResponse($transactions, "OK");
    }

    public function take(int $transaction_id) {
        $transaction = Transaction::find($transaction_id);
        $this->authorize('take', $transaction);
        $user = User::find(auth()->user()->id);
    
        $in_progress = Transaction::where('user_taker', $user->id)->where('status', 'EN PROGRESO')->count();

        if($in_progress > 3)
            return $this->sendError("HAS_IN_PROGRESS");

        if($transaction->status == 'ESPERA')
            $transaction->status = 'EN PROGRESO';
        
        $transaction->user_taker = $user->id;
        $transaction->save();

        if($transaction->type == "AGREGAR") {
            $user->balance -= $transaction->total;
            $user->save();
        }

        return $this->sendResponse("OK", "OK");
    }

    public function complete(int $transaction_id) {
        $user = auth()->user();
        $transaction = Transaction::find($transaction_id);

        if($transaction->status != "PAGADA")
            return $this->sendError("TRANSACTION_NOT_IN_PROGRESS", "TRANSACTION_NOT_IN_PROGRESS", 400);

        switch($transaction->type) {
            case "ENVIAR": return $this->completeSend($transaction);
            case "AGREGAR": return $this->completeAddition($transaction);
            case "RETIRAR": return $this->completeWithdraw($transaction);
            case "INTERCAMBIAR": return $this->completeExchange($transaction);
        }

    }

    public function completeSend(Transaction $transaction) {
        return $this->sendError('NOT_VALID_OPERATION', 'NOT_VALID_OPERATION', 400);
    }

    public function completeWithdraw(Transaction $transaction) {
        $this->authorize('completeWithdraw', $transaction);
        $user = User::find($transaction->user_from); 
        $transaction->status = 'COMPLETA';
        $transaction->save();
        $user->balance -= $transaction->amount;
        $user->save();
        return $this->sendResponse("OK", "OK");
    }

    public function completeAddition(Transaction $transaction) {
        $this->authorize('completeAddition', $transaction);
        $user = User::find($transaction->user_from); 
        $transaction->status = 'COMPLETA';
        $transaction->save();
        $user->balance += $transaction->total;
        $user->save();
        return $this->sendResponse("OK", "OK");
    }

    public function completeExchange(Transaction $transaction) {
        $this->authorize('completeExchange', $transaction);
        $transaction->status = 'COMPLETA';
        $transaction->save();
        return $this->sendResponse("OK", "OK");
    }

    public function showVoucherUserImage(int $id) {
        $transaction = Transaction::find($id);
        $this->authorize('showVoucher', $transaction);
        $image = $transaction->voucher_user;

        return response(
            Storage::get($image),
            200,
            [
                "Content-Type" => Storage::mimeType($image)
            ]
        );
    }

    public function showVoucherCashierImage(int $id) {
        $transaction = Transaction::find($id);
        $this->authorize('showVoucher', $transaction);
        $image = $transaction->voucher_cashier;

        return response(
            Storage::get($image),
            200,
            [
                "Content-Type" => Storage::mimeType($image)
            ]
        );
    }

}
