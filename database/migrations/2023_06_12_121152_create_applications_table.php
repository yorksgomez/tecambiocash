<?php

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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string("name1");
            $table->string("lastname1");
            $table->string("phone1");
            $table->string("email1");
            $table->string("name1");
            $table->string("relationship1");
            $table->string("name2");
            $table->string("lastname2");
            $table->string("phone2");
            $table->string("email2");
            $table->string("name2");
            $table->string("relationship2");
            $table->string("status")->default("ESPERA");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
