<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Payment\Models\Payment;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paypal_transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('payment');
            $table->string('payment_id')->nullable();
            $table->string('token')->nullable();
            $table->string('payer_id')->nullable();
            $table->json('payload')->nullable();
            $table->json('response');
            $table->string('status')->unsigned()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paypal_transactions');
    }
};
