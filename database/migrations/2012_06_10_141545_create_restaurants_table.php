<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('contact_person');
            $table->string('phone');
            $table->string('vat')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('payment_fee')->nullable();
            $table->string('slug')->nullable();
            $table->string('payrexx_token');
            $table->string('company_registration');
            $table->string('payrexx_name');
            $table->string('status');
            $table->integer('onboarded_by')->nullable();
            $table->datetime('onboarded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('restaurants');
    }
};
