<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add columns only if they don't already exist to make this migration safe to run multiple times
        if (!Schema::hasTable('task_histories')) {
            return;
        }

        Schema::table('task_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('task_histories', 'assigned_to')) {
                $table->string('assigned_to')->nullable()->after('status');
            }

            if (!Schema::hasColumn('task_histories', 'task_area')) {
                $table->string('task_area')->nullable()->after('assigned_to');
            }

            if (!Schema::hasColumn('task_histories', 'task_description')) {
                $table->text('task_description')->nullable()->after('task_area');
            }

            if (!Schema::hasColumn('task_histories', 'filter_type')) {
                $table->string('filter_type')->default('daily')->after('task_description');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('task_histories')) {
            return;
        }

        Schema::table('task_histories', function (Blueprint $table) {
            if (Schema::hasColumn('task_histories', 'filter_type')) {
                $table->dropColumn('filter_type');
            }

            if (Schema::hasColumn('task_histories', 'task_description')) {
                $table->dropColumn('task_description');
            }

            if (Schema::hasColumn('task_histories', 'task_area')) {
                $table->dropColumn('task_area');
            }

            if (Schema::hasColumn('task_histories', 'assigned_to')) {
                $table->dropColumn('assigned_to');
            }
        });
    }
};
