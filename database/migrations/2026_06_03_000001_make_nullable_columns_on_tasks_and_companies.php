<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // tasks.priority is validated as nullable in the API controller but was
        // created without a default, causing 500s when priority is not supplied.
        Schema::table('tasks', function (Blueprint $table) {
            $table->integer('priority')->nullable()->change();
        });

        // companies: address, city, state, zip, phone_number were created NOT NULL
        // but are validated as nullable in the API controller.
        Schema::table('companies', function (Blueprint $table) {
            $table->string('address')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('state')->nullable()->change();
            $table->string('zip')->nullable()->change();
            $table->string('phone_number')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->integer('priority')->nullable(false)->change();
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->string('address')->nullable(false)->change();
            $table->string('city')->nullable(false)->change();
            $table->string('state')->nullable(false)->change();
            $table->string('zip')->nullable(false)->change();
            $table->string('phone_number')->nullable(false)->change();
        });
    }
};
