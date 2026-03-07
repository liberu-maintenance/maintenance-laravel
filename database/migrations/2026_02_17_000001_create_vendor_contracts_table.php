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
        Schema::create('vendor_contracts', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id');
            $table->foreign('vendor_id')->references('company_id')->on('companies')->onDelete('cascade');
            $table->string('contract_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('contract_type', ['service', 'maintenance', 'supply', 'other'])->default('service');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('contract_value', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['draft', 'active', 'expired', 'terminated', 'renewed'])->default('draft');
            $table->text('terms_and_conditions')->nullable();
            $table->enum('payment_frequency', ['one_time', 'monthly', 'quarterly', 'annually'])->nullable();
            $table->integer('renewal_period_months')->nullable();
            $table->boolean('auto_renewal')->default(false);
            $table->date('renewal_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('team_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->index('vendor_id');
            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_contracts');
    }
};
