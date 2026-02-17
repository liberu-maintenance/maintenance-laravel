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
            // Add indexes for common filtering and sorting operations
            $table->index('name', 'equipment_name_index');
            $table->index('created_at', 'equipment_created_at_index');
            // Composite indexes already exist for status+criticality and category+location
        });

        Schema::table('work_orders', function (Blueprint $table) {
            // Add composite indexes for common query patterns
            $table->index(['equipment_id', 'status'], 'work_orders_equipment_status_index');
            $table->index(['assigned_to', 'status'], 'work_orders_assigned_status_index');
            $table->index(['status', 'priority'], 'work_orders_status_priority_index');
            $table->index(['team_id', 'status'], 'work_orders_team_status_index');
            $table->index(['customer_id', 'status'], 'work_orders_customer_status_index');
            
            // Index for due date queries (already has single column index, but add composite)
            $table->index(['due_date', 'status'], 'work_orders_due_date_status_index');
        });

        Schema::table('maintenance_schedules', function (Blueprint $table) {
            // Add composite index for priority-based filtering with status
            $table->index(['priority', 'status'], 'maintenance_schedules_priority_status_index');
            $table->index(['team_id', 'status'], 'maintenance_schedules_team_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropIndex('equipment_name_index');
            $table->dropIndex('equipment_created_at_index');
        });

        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropIndex('work_orders_equipment_status_index');
            $table->dropIndex('work_orders_assigned_status_index');
            $table->dropIndex('work_orders_status_priority_index');
            $table->dropIndex('work_orders_team_status_index');
            $table->dropIndex('work_orders_customer_status_index');
            $table->dropIndex('work_orders_due_date_status_index');
        });

        Schema::table('maintenance_schedules', function (Blueprint $table) {
            $table->dropIndex('maintenance_schedules_priority_status_index');
            $table->dropIndex('maintenance_schedules_team_status_index');
        });
    }
};
