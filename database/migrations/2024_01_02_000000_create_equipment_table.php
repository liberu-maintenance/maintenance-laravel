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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('serial_number')->unique()->nullable();
            $table->string('model')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('category')->nullable();
            $table->string('location')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->enum('status', ['active', 'inactive', 'under_maintenance', 'retired'])->default('active');
            $table->enum('criticality', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->text('notes')->nullable();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['status', 'criticality']);
            $table->index(['category', 'location']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};