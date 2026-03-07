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
        Schema::create('vendor_performance_evaluations', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id');
            $table->foreign('vendor_id')->references('company_id')->on('companies')->onDelete('cascade');
            $table->foreignId('vendor_contract_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('work_order_id')->nullable()->constrained()->onDelete('set null');
            $table->date('evaluation_date');
            $table->foreignId('evaluated_by')->constrained('users')->onDelete('cascade');
            $table->integer('quality_rating')->default(0); // 1-5 scale
            $table->integer('timeliness_rating')->default(0); // 1-5 scale
            $table->integer('communication_rating')->default(0); // 1-5 scale
            $table->integer('cost_effectiveness_rating')->default(0); // 1-5 scale
            $table->integer('professionalism_rating')->default(0); // 1-5 scale
            $table->decimal('overall_rating', 3, 2)->default(0.00); // Calculated average
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('comments')->nullable();
            $table->boolean('would_recommend')->default(true);
            $table->foreignId('team_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->index('vendor_id');
            $table->index('evaluation_date');
            $table->index('overall_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_performance_evaluations');
    }
};
