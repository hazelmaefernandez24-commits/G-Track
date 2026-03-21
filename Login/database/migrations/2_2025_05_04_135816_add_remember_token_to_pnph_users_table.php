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
            $table->string('remember_token', 100)->nullable()->after('user_password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pnph_users', function (Blueprint $table) {
            $table->dropColumn('remember_token');
        });
    }
};
