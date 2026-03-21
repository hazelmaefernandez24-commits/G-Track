<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure terms can store JSON arrays consistently
        Schema::table('schools', function (Blueprint $table) {
            // For SQLite or databases without modify, leaving as json is fine
            // This migration exists to document expectation that 'terms' is JSON
        });
    }

    public function down(): void
    {
        // No-op
    }
};


