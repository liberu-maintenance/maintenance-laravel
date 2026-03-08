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
            if (Schema::hasColumn('companies', 'statte')) {
                $table->renameColumn('statte', 'state');
            }

            if (Schema::hasColumn('companies', 'zip')) {
                $table->string('zip')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'state')) {
                $table->renameColumn('state', 'statte');
            }

            if (Schema::hasColumn('companies', 'zip')) {
                $table->integer('zip')->change();
            }
        });
    }
};
