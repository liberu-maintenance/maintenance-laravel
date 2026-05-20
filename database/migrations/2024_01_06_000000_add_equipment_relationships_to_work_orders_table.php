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
        Schema::table('work_orders', function (Blueprint $table) {
            $table->foreignId('equipment_id')->nullable()->after('equipment')->constrained()->onDelete('set null');
            $table->foreignId('maintenance_schedule_id')->nullable()->after('equipment_id')->constrained()->onDelete('set null');
            $table->foreignId('checklist_id')->nullable()->after('maintenance_schedule_id')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropForeign(['equipment_id']);
            $table->dropForeign(['maintenance_schedule_id']);
            $table->dropForeign(['checklist_id']);
            $table->dropColumn(['equipment_id', 'maintenance_schedule_id', 'checklist_id']);
        });
    }
};