<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            // JSON array of weekdays (e.g., ["Monday","Wednesday"]) – nullable for backward compatibility
            if (!Schema::hasColumn('internships', 'days')) {
                $table->json('days')->nullable()->after('company');
            }
        });
    }

    public function down(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            if (Schema::hasColumn('internships', 'days')) {
                $table->dropColumn('days');
            }
        });
    }
};


