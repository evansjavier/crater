<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReturnItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description')->nullable();
            // $table->string('discount_type');
            $table->unsignedBigInteger('price');
            $table->decimal('quantity', 15, 2);
            // $table->decimal('discount', 15, 2)->nullable();
            // $table->unsignedBigInteger('discount_val');
            // $table->unsignedBigInteger('tax');
            $table->unsignedBigInteger('total');
            $table->integer('return_id')->unsigned();
            $table->foreign('return_id')->references('id')->on('returns')->onDelete('restrict');
            $table->integer('item_id')->unsigned()->nullable();
            $table->foreign('item_id')->references('id')->on('items')->onDelete('restrict');
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('restrict');
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
        Schema::dropIfExists('return_items');
    }
}
