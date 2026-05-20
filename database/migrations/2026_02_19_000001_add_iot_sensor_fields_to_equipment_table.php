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
        Schema::table('equipment', function (Blueprint $table) {
            $table->boolean('sensor_enabled')->default(false)->after('notes');
            $table->string('sensor_type')->nullable()->after('sensor_enabled');
            $table->string('sensor_id')->nullable()->unique()->after('sensor_type');
            $table->json('sensor_config')->nullable()->after('sensor_id');
            $table->timestamp('last_sensor_reading_at')->nullable()->after('sensor_config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn([
                'sensor_enabled',
                'sensor_type',
                'sensor_id',
                'sensor_config',
                'last_sensor_reading_at',
            ]);
        });
    }
};
