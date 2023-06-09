<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Models\BankAccount;
use App\Models\Configuration;
use App\Models\CurrencyValue;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ConfigurationController extends BaseController
{

    public function show() {
        $this->authorize('viewAny', Configuration::class);
        return $this->sendResponse(Configuration::find(1), "OK");
    }

    public function update(Request $request) {
        $this->authorize('update', Configuration::class);
        $data = $request->all();
        $config = Configuration::find(1);
        $config->update($data);
        $config->save();
        return $this->sendResponse("OK", "OK");
    }

}
