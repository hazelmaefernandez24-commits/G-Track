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
        Schema::table('pnph_users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('user_role'); // Add the is_active column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pnph_users', function (Blueprint $table) {
            $table->dropColumn('is_active'); // Remove the is_active column if rolled back
        });
    }
};
