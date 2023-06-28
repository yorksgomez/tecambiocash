<?php

use App\Models\CurrencyValue;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, "user_from");
            $table->foreignIdFor(CurrencyValue::class, "currency_from_id");
            $table->foreignIdFor(CurrencyValue::class, "currency_to_id")->nullable();
            $table->foreignIdFor(User::class, "user_taker")->nullable();
            $table->double("amount");
            $table->double("total");
            $table->double("comission");
            $table->string("type");
            $table->string("status");
            $table->string("voucher")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
