<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'cashier_verified_by')) {
                $table->string('cashier_verified_by', 255)->nullable()->after('verified_by');
            }

            if (!Schema::hasColumn('payments', 'cashier_verified_at')) {
                if (Schema::hasColumn('payments', 'verified_at')) {
                    $table->timestamp('cashier_verified_at')->nullable()->after('verified_at');
                } else {
                    $table->timestamp('cashier_verified_at')->nullable(); // fallback if `verified_at` doesn't exist
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'cashier_verified_by')) {
                $table->dropColumn('cashier_verified_by');
            }

            if (Schema::hasColumn('payments', 'cashier_verified_at')) {
                $table->dropColumn('cashier_verified_at');
            }
        });
    }
};
