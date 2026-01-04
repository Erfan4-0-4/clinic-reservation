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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->string('appointment_date');
            $table->string('start_time');
            $table->string('end_time');
            $table->enum('status', ['reserved', 'cancelled', 'completed'])->default('reserved');
            $table->string('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
