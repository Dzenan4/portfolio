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
        Schema::create('customers', function (Blueprint $table) {
            $table->unsignedSmallInteger('id', true, true);
            $table->string('first_name');
            $table->string('middle_initial', 1)->nullable(); 
            $table->string('last_name');
            $table->string('address_street');
            $table->string('address_city');
            $table->string('address_state', 2); 
            $table->string('address_zip', 10);
            $table->string('phone_number', 10)->nullable();
            $table->string('dl_number')->unique()->nullable();
            $table->string('dl_state', 2)->nullable(); // State abbreviation
            $table->date('dob')->nullable();
            $table->string('dl_picture_link')->nullable(); // Assuming a URL or path
            $table->string('self_picture_link')->nullable(); // Assuming a URL or path
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
