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
        if (!Schema::hasTable('kitchen_poll_responses')) {
            Schema::create('kitchen_poll_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained('kitchen_menu_polls')->onDelete('cascade');
            $table->string('student_id');
            $table->foreign('student_id')->references('user_id')->on('pnph_users')->onDelete('cascade');
            $table->boolean('will_eat')->default(false);
            $table->text('notes')->nullable();
            $table->timestamp('responded_at')->useCurrent();
            $table->timestamps();

            // Ensure one response per student per poll
            $table->unique(['poll_id', 'student_id']);
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_poll_responses');
    }
};
