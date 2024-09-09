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
        Schema::table('orders', function (Blueprint $table) {
            $table->dateTime('restaurant_ticket_requested_at')->after('restaurant_ticket_printed')->nullable();
            $table->ipAddress('restaurant_ticket_requested_by')->after('restaurant_ticket_requested_at')->nullable();
            $table->dateTime('bar_ticket_requested_at')->after('bar_ticket_printed')->nullable();
            $table->ipAddress('bar_ticket_requested_by')->after('bar_ticket_requested_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('restaurant_ticket_requested_at');
            $table->dropColumn('restaurant_ticket_requested_by');
            $table->dropColumn('bar_ticket_requested_at');
            $table->dropColumn('bar_ticket_requested_by');
        });
    }
};
