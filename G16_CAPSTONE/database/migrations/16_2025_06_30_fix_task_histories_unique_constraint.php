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
        Schema::table('task_histories', function (Blueprint $table) {
            // Drop the old unique constraint
            $table->dropUnique(['room_number', 'day', 'week', 'month', 'year']);
            
            // Add new unique constraint that includes task_id
            $table->unique(['room_number', 'task_id', 'day', 'week', 'month', 'year'], 'task_histories_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_histories', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique('task_histories_unique');
            
            // Restore the old unique constraint
            $table->unique(['room_number', 'day', 'week', 'month', 'year']);
        });
    }
};
