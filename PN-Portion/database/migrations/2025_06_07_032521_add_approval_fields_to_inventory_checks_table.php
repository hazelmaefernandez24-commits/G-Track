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
        Schema::table('inventory_checks', function (Blueprint $table) {
            $table->timestamp('approved_at')->nullable();
            $table->string('approved_by')->nullable();
            $table->text('approval_notes')->nullable();

            $table->foreign('approved_by')->references('user_id')->on('pnph_users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_checks', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approved_at', 'approved_by', 'approval_notes']);
        });
    }
};
