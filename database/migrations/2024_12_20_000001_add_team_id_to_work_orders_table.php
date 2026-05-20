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
            // Add team_id column
            if (!Schema::hasColumn('work_orders', 'team_id')) {
                $table->foreignId('team_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            }

            // Only add equipment_id if it doesn't exist
            if (!Schema::hasColumn('work_orders', 'equipment_id')) {
                $table->foreignId('equipment_id')->nullable()->after('team_id')->constrained()->onDelete('set null');
            }

            // Only add maintenance_schedule_id if it doesn't exist
            if (!Schema::hasColumn('work_orders', 'maintenance_schedule_id')) {
                $table->foreignId('maintenance_schedule_id')->nullable()->after('equipment_id')->constrained()->onDelete('set null');
            }

            // Only add checklist_id if it doesn't exist
            if (!Schema::hasColumn('work_orders', 'checklist_id')) {
                $table->foreignId('checklist_id')->nullable()->after('maintenance_schedule_id')->constrained()->onDelete('set null');
            }
        });

        // Remove the old equipment string column in a separate schema operation
        if (Schema::hasColumn('work_orders', 'equipment')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $table->dropColumn('equipment');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the equipment string column first
        if (!Schema::hasColumn('work_orders', 'equipment')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $table->string('equipment')->nullable();
            });
        }

        Schema::table('work_orders', function (Blueprint $table) {
            // Drop foreign keys and columns that exist
            if (Schema::hasColumn('work_orders', 'team_id')) {
                $table->dropForeign(['team_id']);
                $table->dropColumn('team_id');
            }

            if (Schema::hasColumn('work_orders', 'equipment_id')) {
                $table->dropForeign(['equipment_id']);
                $table->dropColumn('equipment_id');
            }

            if (Schema::hasColumn('work_orders', 'maintenance_schedule_id')) {
                $table->dropForeign(['maintenance_schedule_id']);
                $table->dropColumn('maintenance_schedule_id');
            }

            if (Schema::hasColumn('work_orders', 'checklist_id')) {
                $table->dropForeign(['checklist_id']);
                $table->dropColumn('checklist_id');
            }
        });
    }
};