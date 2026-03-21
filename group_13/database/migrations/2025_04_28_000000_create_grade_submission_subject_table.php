<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('grade_submission_subject', function (Blueprint $table) {
            $table->id();
            $table->string('grade')->nullable();
            $table->enum('status', ['pending', 'submitted', 'approved', 'rejected'])->default('pending');
            $table->string('user_id');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('grade_submission_subject', function (Blueprint $table) {
            if (Schema::hasColumn('grade_submission_subject', 'grade')) {
                $table->dropColumn('grade');
            }
            if (Schema::hasColumn('grade_submission_subject', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('grade_submission_subject', 'user_id')) {
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('grade_submission_subject', 'created_at')) {
                $table->dropTimestamps();
            }
        });
    }
};