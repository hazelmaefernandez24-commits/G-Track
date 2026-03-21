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
        if (!Schema::hasTable('advance_schedule')) {
            Schema::create('advance_schedule', function (Blueprint $table) {
                $table->id();
                $table->string('event_name');
                $table->string('year_level');
                $table->date('date_from');
                $table->date('date_to');
                $table->time('time_start');
                $table->time('time_end')->nullable();
                $table->date('added_on');
                $table->string('added_by');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advance_schedule');
    }
};
