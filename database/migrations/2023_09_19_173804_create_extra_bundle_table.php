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
        Schema::create('extra_bundle', function (Blueprint $table) {
            $table->id();
            $table->integer('entity_id');
            $table->string('entity_type');
            $table->foreignId('bundle_id')->constrained()->onDelete('cascade');
            $table->decimal('price')->nullable();
            $table->integer('order')->nullable();
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
        Schema::dropIfExists('extra_bundle');
    }
};
