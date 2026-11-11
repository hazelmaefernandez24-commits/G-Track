<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('admins', 'middle_initial')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->string('middle_initial', 5)->nullable()->after('first_name');
            });
        }
    }

    public function down(): void
    {
        // The column is owned by the original name-parts migration.
    }
};
