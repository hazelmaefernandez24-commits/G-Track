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
    public function up(): void
    {
        Schema::create('roomtask', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Student name or task name
            $table->string('room_number')->nullable();
            $table->string('area'); // Task area e.g., Kitchen, Office
            $table->text('desc'); // Task description
            $table->string('day'); // Day of the week
            $table->string('status')->default('unchecked');
            $table->string('week')->nullable(); // Week number
            $table->string('month')->nullable(); // Month number
            $table->string('year')->nullable(); // Year
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('roomtask');
    }
};
