<?php

use App\Models\Order;
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
        Schema::create('applied_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class);
            $table->string('source', 30);
            $table->string('type', 30);
            $table->float('amount');
            $table->float('target_sum');
            $table->float('net');
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
        Schema::dropIfExists('applied_discounts');
    }
};
