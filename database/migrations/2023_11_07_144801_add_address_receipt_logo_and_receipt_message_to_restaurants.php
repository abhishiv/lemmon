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
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('address')->after('phone');
            $table->string('receipt_logo')->after('payrexx_name')->nullable();
            $table->string('receipt_message')->after('receipt_logo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('address');
            $table->dropColumn('receipt_logo');
            $table->dropColumn('receipt_message');
        });
    }
};
