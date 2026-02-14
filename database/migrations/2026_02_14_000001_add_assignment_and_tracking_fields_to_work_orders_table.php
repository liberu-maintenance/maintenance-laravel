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
            // Assignment tracking
            $table->foreignId('assigned_to')->nullable()->after('team_id')->constrained('users')->onDelete('set null');
            
            // Due date for SLA management
            $table->dateTime('due_date')->nullable()->after('submitted_at');
            
            // Work progress timestamps
            $table->dateTime('started_at')->nullable()->after('reviewed_at');
            $table->dateTime('completed_at')->nullable()->after('started_at');
            
            // Labor hour tracking
            $table->unsignedInteger('estimated_hours')->nullable()->after('completed_at');
            $table->unsignedInteger('actual_hours')->nullable()->after('estimated_hours');
            
            // Add indexes for common queries
            $table->index('status');
            $table->index('priority');
            $table->index(['status', 'created_at']);
            $table->index('assigned_to');
            $table->index('due_date');
            $table->index('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['status']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['assigned_to']);
            $table->dropIndex(['due_date']);
            $table->dropIndex(['submitted_at']);
            
            // Drop foreign key and column
            $table->dropForeign(['assigned_to']);
            
            // Drop columns
            $table->dropColumn([
                'assigned_to',
                'due_date',
                'started_at',
                'completed_at',
                'estimated_hours',
                'actual_hours',
            ]);
        });
    }
};
