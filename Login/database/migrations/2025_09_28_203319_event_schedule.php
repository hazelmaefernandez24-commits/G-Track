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
        Schema::create('event_schedule', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('calendar_events_id')->index();
            $table->time('time_out')->nullable();
            $table->time('time_in')->nullable();
            $table->string('created_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->boolean('is_deleted')->default(0);

            $table->foreign('calendar_events_id')->references('id')->on('calendar_events')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_schedule');
    }
};
