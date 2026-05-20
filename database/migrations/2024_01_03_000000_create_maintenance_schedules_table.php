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
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('equipment_id')->constrained()->onDelete('cascade');
            $table->enum('frequency_type', ['daily', 'weekly', 'monthly', 'yearly', 'hours'])->default('monthly');
            $table->integer('frequency_value')->default(1);
            $table->date('next_due_date');
            $table->date('last_completed_date')->nullable();
            $table->integer('estimated_duration')->nullable()->comment('Duration in minutes');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['active', 'inactive', 'completed'])->default('active');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->text('instructions')->nullable();
            $table->foreignId('checklist_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();

            $table->index(['next_due_date', 'status']);
            $table->index(['equipment_id', 'status']);
            $table->index(['assigned_to', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};