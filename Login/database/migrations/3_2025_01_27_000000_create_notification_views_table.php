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
        Schema::create('notification_views', function (Blueprint $table) {
            $table->id();
            $table->string('log_type'); // 'academic' or 'goingout' or 'visitor'
            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamps();

            $table->unique('log_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_views2');
    }
};
