<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->string('order_number');
            $table->integer('order_total');
            $table->integer('order_tax');
            $table->integer('order_delivery');
            $table->integer('order_grand_total');
            $table->integer('address');
            $table->double('lat', 10, 7);
            $table->double('lon', 10, 7);
            $table->double('distance', 10, 3);
            $table->enum('payment_method', ['credit_card'])->default('credit_card');
            $table->string('receipt')->nullable();
            $table->timestamps();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
