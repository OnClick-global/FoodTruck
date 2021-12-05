<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegisterCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('register_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('title',100);
            $table->string('code',15);
            $table->tinyInteger('expire')->default(0);
            $table->decimal('discount_annual')->nullable();
            $table->decimal('discount_percentage')->nullable();
            $table->foreignId('restaurant_id')->nullable();
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
        Schema::dropIfExists('register_coupons');
    }
}
