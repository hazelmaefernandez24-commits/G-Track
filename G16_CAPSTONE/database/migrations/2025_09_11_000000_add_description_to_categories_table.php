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
        // Ensure we modify the 'categories' table on the 'login' connection
        Schema::connection('login')->table('categories', function (Blueprint $table) {
            if (! Schema::connection('login')->hasColumn('categories', 'description')) {
                $table->string('description')->nullable()->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('login')->table('categories', function (Blueprint $table) {
            if (Schema::connection('login')->hasColumn('categories', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
