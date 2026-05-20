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
        Schema::table('iot_sensor_readings', function (Blueprint $table) {
            $table->string('sensor_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('iot_sensor_readings', function (Blueprint $table) {
            $table->string('sensor_type')->nullable(false)->change();
        });
    }
};
