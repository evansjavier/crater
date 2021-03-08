<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceReturnIdToTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->unsignedInteger('invoice_return_id')->nullable()->after('invoice_id');
            $table->foreign('invoice_return_id')->references('id')->on('invoice_returns')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->dropForeign(['invoice_return_id']);
            $table->dropColumn('invoice_return_id');
        });
    }
}