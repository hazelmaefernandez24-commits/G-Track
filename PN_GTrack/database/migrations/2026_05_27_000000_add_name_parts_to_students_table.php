<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('student_id');
            $table->string('middle_initial', 5)->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_initial');
        });

        // Backfill from existing `name` column where possible
        DB::table('students')->select('id', 'name')->orderBy('id')->chunk(100, function ($rows) {
            foreach ($rows as $r) {
                $parts = preg_split('/\s+/', trim($r->name));
                $first = null;
                $middle = null;
                $last = null;
                if (count($parts) === 1) {
                    $first = $parts[0];
                } elseif (count($parts) === 2) {
                    $first = $parts[0];
                    $last = $parts[1];
                } elseif (count($parts) >= 3) {
                    $first = $parts[0];
                    $last = array_pop($parts);
                    $middle = '';
                    // take first letters of middle parts joined
                    foreach (array_slice($parts, 1) as $p) { $middle .= substr($p,0,1); }
                }
                DB::table('students')->where('id', $r->id)->update([
                    'first_name' => $first,
                    'middle_initial' => $middle,
                    'last_name' => $last,
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'middle_initial', 'last_name']);
        });
    }
};
