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
        Schema::table('violations', function (Blueprint $table) {
            // Change action_taken default from false to true
            $table->boolean('action_taken')->default(true)->change();
        });

        // Update existing records to have action_taken = true
        DB::table('violations')->update(['action_taken' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            // Revert action_taken default back to false
            $table->boolean('action_taken')->default(false)->change();
        });
    }
};
