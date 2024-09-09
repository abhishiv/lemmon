<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stripe_transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('payment_id')->unsigned();
            $table->string('session_id')->nullable()->unique();
            $table->string('payment_intent_id')->nullable()->unique();
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->tinyInteger('status')->unsigned();
            $table->timestamps();

            $table->foreign('payment_id')->references('id')->on('payments');
            $table->unique(['payment_id', 'status'], 'unique_payment_transaction');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stripe_transactions');
    }
};
