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
        Schema::create('sensor_data', function (Blueprint $table) {
            $table->id();
            $table->string('device_id');
            $table->string('sensor_type'); // flame, temperature, gas, etc
            $table->json('raw_data'); // ESP32 sensor readings
            $table->json('ml_results')->nullable(); // ML API response
            $table->string('status')->default('processing'); // processing, completed, failed
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        
            $table->index('device_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_data');
    }
};
