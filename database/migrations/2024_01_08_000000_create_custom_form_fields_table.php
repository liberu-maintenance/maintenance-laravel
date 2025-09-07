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
        Schema::create('custom_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_form_id')->constrained()->onDelete('cascade');
            $table->string('label');
            $table->string('name');
            $table->enum('type', ['text', 'email', 'number', 'textarea', 'select', 'checkbox', 'radio', 'date', 'time', 'file'])->default('text');
            $table->boolean('required')->default(false);
            $table->string('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->json('options')->nullable();
            $table->json('validation_rules')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['custom_form_id', 'order']);
            $table->index(['type', 'required']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_form_fields');
    }
};