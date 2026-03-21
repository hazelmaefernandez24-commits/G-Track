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
            $table->string('user_mInitial')->nullable()->change(); // Make user_mInitial nullable
            $table->string('user_suffix')->nullable()->change();   // Make user_suffix nullable
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pnph_users', function (Blueprint $table) {
            $table->string('user_mInitial')->nullable(false)->change(); // Revert user_mInitial to not nullable
            $table->string('user_suffix')->nullable(false)->change();   // Revert user_suffix to not nullable
        });
    }
};
