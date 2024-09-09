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
        Schema::create('payments_partial', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('table_id');
            $table->integer('restaurant_id');
            $table->string('orders');
            $table->string('tips')->nullable();
            $table->string('discount')->nullable();
            $table->string('discount_type')->nullable();
            $table->string('amount');
            $table->longText('cart');
            $table->longText('selection');
            $table->string('method');
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
        Schema::dropIfExists('payments_partial');
    }
};
