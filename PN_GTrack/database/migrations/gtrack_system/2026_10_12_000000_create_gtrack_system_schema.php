<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('gtrack_system')->create('student_classes', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year')->unique();
            $table->timestamps();
        });

        Schema::connection('gtrack_system')->create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('gender');
            $table->foreignId('student_class_id')->constrained('student_classes')->restrictOnDelete();
            $table->string('contact')->nullable();
            $table->string('profile_picture')->nullable();
            $table->timestamps();
        });

        Schema::connection('gtrack_system')->create('student_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('status');
            $table->string('sos_status');
            $table->timestamp('recorded_at');
            $table->index(['student_id', 'recorded_at']);
        });

        Schema::connection('gtrack_system')->create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->timestamp('recorded_at');
            $table->index(['student_id', 'recorded_at']);
        });

        Schema::connection('gtrack_system')->create('student_auths', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
            $table->foreign('student_id')
                ->references('student_id')
                ->on('students')
                ->cascadeOnDelete();
        });

        Schema::connection('gtrack_system')->create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('staff_id')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role');
            $table->timestamps();
        });

        Schema::connection('gtrack_system')->create('emergency_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('assigned_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('type');
            $table->text('description')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('read_at')->nullable();
            $table->foreignId('acknowledged_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('resolved_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::connection('gtrack_system')->create('alert_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emergency_alert_id')->constrained('emergency_alerts')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('mime_type');
            $table->timestamps();
        });

        Schema::connection('gtrack_system')->create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('subject')->nullable();
            $table->timestamps();
        });

        Schema::connection('gtrack_system')->create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->string('sender_type');
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->foreignId('reply_to_id')->nullable()->constrained('messages')->nullOnDelete();
            $table->timestamps();
            $table->index(['sender_type', 'sender_id']);
        });

        Schema::connection('gtrack_system')->create('broadcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->restrictOnDelete();
            $table->string('subject');
            $table->text('message');
            $table->timestamps();
        });

        Schema::connection('gtrack_system')->create('broadcast_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broadcast_id')->constrained('broadcasts')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->unique(['broadcast_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('gtrack_system')->dropIfExists('broadcast_recipients');
        Schema::connection('gtrack_system')->dropIfExists('broadcasts');
        Schema::connection('gtrack_system')->dropIfExists('messages');
        Schema::connection('gtrack_system')->dropIfExists('conversations');
        Schema::connection('gtrack_system')->dropIfExists('alert_media');
        Schema::connection('gtrack_system')->dropIfExists('emergency_alerts');
        Schema::connection('gtrack_system')->dropIfExists('student_auths');
        Schema::connection('gtrack_system')->dropIfExists('locations');
        Schema::connection('gtrack_system')->dropIfExists('student_statuses');
        Schema::connection('gtrack_system')->dropIfExists('students');
        Schema::connection('gtrack_system')->dropIfExists('admins');
        Schema::connection('gtrack_system')->dropIfExists('student_classes');
    }
};
