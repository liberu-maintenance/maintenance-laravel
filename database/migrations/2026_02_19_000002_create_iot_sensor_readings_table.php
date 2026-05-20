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
        Schema::create('iot_sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
            $table->string('sensor_type');
            $table->string('metric_name');
            $table->decimal('value', 10, 2);
            $table->string('unit')->nullable();
            $table->json('metadata')->nullable();
            $table->enum('status', ['normal', 'warning', 'critical', 'error'])->default('normal');
            $table->timestamp('reading_time');
            $table->timestamps();

            $table->index(['equipment_id', 'reading_time']);
            $table->index(['equipment_id', 'metric_name', 'reading_time']);
            $table->index(['status', 'reading_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iot_sensor_readings');
    }
};
