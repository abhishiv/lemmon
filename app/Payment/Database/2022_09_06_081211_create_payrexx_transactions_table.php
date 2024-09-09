<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * reference_id => Order id
     * token => gateway id (response id)
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payrexx_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id')->nullable();
            $table->string('token')->nullable();
            $table->string('reference_id')->nullable();
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->string('status')->index();
            $table->timestamps();

            $table->foreign('payment_id')->references('id')->on('payments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payrexx_transactions');
    }
};
