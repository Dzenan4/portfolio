<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->unsignedInteger('id', true, true);
            $table->unsignedSmallInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->unsignedInteger('check_id');
            $table->date('date')->nullable()->default(DB::raw('CURRENT_DATE'));
            $table->foreign('check_id')->references('id')->on('checks');
            $table->float('check_amount', 7, 2);
            $table->float('payout_amount', 7, 2);
            $table->float('charge_amount', 7, 2);
            $table->float('charge_percentage', 4, 2);
            $table->string('check_number');
            $table->string('check_picture_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }

};
