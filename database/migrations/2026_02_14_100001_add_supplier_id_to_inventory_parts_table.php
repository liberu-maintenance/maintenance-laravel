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
        Schema::table('inventory_parts', function (Blueprint $table) {
            // Add new supplier_id column (unsignedInteger to match companies.company_id type)
            $table->unsignedInteger('supplier_id')->nullable()->after('location');
            $table->foreign('supplier_id')->references('company_id')->on('companies')->onDelete('set null');
            
            // Keep the old supplier string column for backward compatibility during migration
            // It can be removed in a future migration after data is migrated
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_parts', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });
    }
};
