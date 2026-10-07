<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('acknowledged_by_admin_id')->nullable();
            $table->string('acknowledged_by_name')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->unsignedBigInteger('resolved_by_admin_id')->nullable();
            $table->string('resolved_by_name')->nullable();
            $table->timestamp('resolved_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn([
                'acknowledged_by_admin_id',
                'acknowledged_by_name',
                'acknowledged_at',
                'resolved_by_admin_id',
                'resolved_by_name',
                'resolved_at',
            ]);
        });
    }
};