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
        Schema::create('checks', function (Blueprint $table) {
            $table->unsignedInteger('id', true, true);
            $table->string('company_name');
            $table->string('company_address_street')->nullable();
            $table->string('company_address_city')->nullable();
            $table->string('company_address_state')->nullable();
            $table->string('company_address_zip')->nullable();
            $table->string('account_number');
            $table->string('routing_number');
            $table->enum('type', ['government', 'personal', 'business', 'insurance', 'cashiers', 'other'])->default('business');
            $table->enum('cashing_status', ['positive', 'unverified', 'negative'])->default('unverified');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checks');
    }
};
