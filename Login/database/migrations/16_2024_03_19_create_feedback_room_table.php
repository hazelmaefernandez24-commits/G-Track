<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if table exists first
        if (!Schema::hasTable('feedback_room')) {
            Schema::create('feedback_room', function (Blueprint $table) {
                $table->id();
                $table->string('room_number', 50);
                $table->text('feedback');
                $table->json('photo_paths')->nullable();
                $table->string('day');
                $table->string('week', 20)->nullable();
                $table->string('month', 20)->nullable();
                $table->string('year', 10)->nullable();
                $table->timestamps();

                // Add indexes for better performance
                $table->index(['room_number', 'day']);
                $table->index(['room_number', 'week', 'month', 'year']);
            });
        } else {
            // Modify existing table
            Schema::table('feedback_room', function (Blueprint $table) {
                // Add any missing columns
                if (!Schema::hasColumn('feedback_room', 'week')) {
                    $table->string('week')->nullable();
                }
                if (!Schema::hasColumn('feedback_room', 'month')) {
                    $table->string('month')->nullable();
                }
                if (!Schema::hasColumn('feedback_room', 'year')) {
                    $table->string('year')->nullable();
                }
                if (!Schema::hasColumn('feedback_room', 'photo_paths')) {
                    $table->json('photo_paths')->nullable();
                }

                // Add indexes if they don't exist
                if (!Schema::hasIndex('feedback_room', ['room_number', 'day'])) {
                    $table->index(['room_number', 'day']);
                }
                if (!Schema::hasIndex('feedback_room', ['room_number', 'week', 'month', 'year'])) {
                    $table->index(['room_number', 'week', 'month', 'year']);
                }
            });
        }
    }

    public function down()
    {
        // Don't drop the table in down() to prevent data loss
        Schema::table('feedback_room', function (Blueprint $table) {
            // Remove indexes
            $table->dropIndex(['room_number', 'day']);
            $table->dropIndex(['room_number', 'week', 'month', 'year']);
        });
    }
};
