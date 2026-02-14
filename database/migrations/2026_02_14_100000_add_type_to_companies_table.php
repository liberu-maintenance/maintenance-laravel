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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('type')->default('customer')->after('description');
            $table->string('contact_person')->nullable()->after('type');
            $table->string('email')->nullable()->after('contact_person');
            $table->text('payment_terms')->nullable()->after('email');
            $table->boolean('is_active')->default(true)->after('payment_terms');
            
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['type', 'contact_person', 'email', 'payment_terms', 'is_active']);
        });
    }
};
