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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('document_type'); // manual, service_record, compliance, procedure, checklist, etc.
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->bigInteger('file_size')->nullable(); // in bytes
            $table->string('version')->default('1.0');
            $table->enum('status', ['draft', 'active', 'archived', 'obsolete'])->default('active');
            
            // Compliance and regulatory fields
            $table->string('compliance_standard')->nullable(); // ISO, OSHA, etc.
            $table->date('effective_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->date('review_date')->nullable();
            $table->string('approval_status')->default('pending'); // pending, approved, rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Relationships - polymorphic for flexibility
            $table->morphs('documentable'); // Can be attached to Equipment, WorkOrder, MaintenanceSchedule, etc.
            
            // Team association for multi-tenancy
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('cascade');
            
            // Metadata
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better performance
            $table->index(['document_type', 'status']);
            $table->index(['team_id', 'status']);
            $table->index(['expiry_date', 'status']);
            $table->index(['documentable_type', 'documentable_id']);
            $table->fullText(['name', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
