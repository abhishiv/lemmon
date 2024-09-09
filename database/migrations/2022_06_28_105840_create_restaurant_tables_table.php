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
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('restaurant_id');
            $table->string('hash');
            $table->string('type');
            $table->string('description', 500)->nullable();
            $table->string('optional', 500)->nullable();
            $table->string('status');
            $table->unique(['name', 'restaurant_id'], 'unique_table_name');
            $table->unique(['hash', 'restaurant_id'], 'unique_table_hash');
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
        Schema::dropIfExists('restaurant_tables');
    }
};
