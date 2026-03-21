<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new 
class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('task_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('task_histories', 'assigned_to')) {
                $table->string('assigned_to')->nullable()->after('room_number');
            }
            if (!Schema::hasColumn('task_histories', 'task_area')) {
                $table->string('task_area')->nullable()->after('assigned_to');
            }
            if (!Schema::hasColumn('task_histories', 'task_description')) {
                $table->text('task_description')->nullable()->after('task_area');
            }
            if (!Schema::hasColumn('task_histories', 'filter_type')) {
                $table->string('filter_type')->default('daily')->after('status');
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
        Schema::table('task_histories', function (Blueprint $table) {
            $table->dropColumn([
                'assigned_to',
                'task_area',
                'task_description',
                'filter_type'
            ]);
        });
    }
};