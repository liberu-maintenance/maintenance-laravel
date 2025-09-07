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
        Schema::create('custom_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_form_id')->constrained()->onDelete('cascade');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->json('data');
            $table->enum('status', ['pending', 'approved', 'rejected', 'in_progress'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['custom_form_id', 'status']);
            $table->index(['submitted_by', 'status']);
            $table->index(['reviewed_by', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_form_submissions');
    }
};