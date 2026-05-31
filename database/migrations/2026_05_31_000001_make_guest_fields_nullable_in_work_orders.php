<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('guest_name')->nullable()->change();
            $table->string('guest_email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('guest_name')->nullable(false)->change();
            $table->string('guest_email')->nullable(false)->change();
        });
    }
};
