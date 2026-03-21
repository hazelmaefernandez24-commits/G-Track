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
        // Update Going Out logs - standardize to proper case
        DB::table('going_outs')
            ->where('time_out_remark', 'ontime')
            ->update(['time_out_remark' => 'On Time']);
            
        DB::table('going_outs')
            ->where('time_out_remark', 'late')
            ->update(['time_out_remark' => 'Late']);
            
        DB::table('going_outs')
            ->where('time_out_remark', 'early')
            ->update(['time_out_remark' => 'Early']);
            
        DB::table('going_outs')
            ->where('time_in_remark', 'ontime')
            ->update(['time_in_remark' => 'On Time']);
            
        DB::table('going_outs')
            ->where('time_in_remark', 'late')
            ->update(['time_in_remark' => 'Late']);
            
        DB::table('going_outs')
            ->where('time_in_remark', 'early')
            ->update(['time_in_remark' => 'Early']);

        // Update Academic logs - standardize to proper case
        DB::table('academics')
            ->where('time_out_remark', 'ontime')
            ->update(['time_out_remark' => 'On Time']);
            
        DB::table('academics')
            ->where('time_out_remark', 'late')
            ->update(['time_out_remark' => 'Late']);
            
        DB::table('academics')
            ->where('time_out_remark', 'early')
            ->update(['time_out_remark' => 'Early']);
            
        DB::table('academics')
            ->where('time_in_remark', 'ontime')
            ->update(['time_in_remark' => 'On Time']);
            
        DB::table('academics')
            ->where('time_in_remark', 'late')
            ->update(['time_in_remark' => 'Late']);
            
        DB::table('academics')
            ->where('time_in_remark', 'early')
            ->update(['time_in_remark' => 'Early']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert Going Out logs back to lowercase
        DB::table('going_outs')
            ->where('time_out_remark', 'On Time')
            ->update(['time_out_remark' => 'ontime']);
            
        DB::table('going_outs')
            ->where('time_out_remark', 'Late')
            ->update(['time_out_remark' => 'late']);
            
        DB::table('going_outs')
            ->where('time_out_remark', 'Early')
            ->update(['time_out_remark' => 'early']);
            
        DB::table('going_outs')
            ->where('time_in_remark', 'On Time')
            ->update(['time_in_remark' => 'ontime']);
            
        DB::table('going_outs')
            ->where('time_in_remark', 'Late')
            ->update(['time_in_remark' => 'late']);
            
        DB::table('going_outs')
            ->where('time_in_remark', 'Early')
            ->update(['time_in_remark' => 'early']);

        // Revert Academic logs back to lowercase
        DB::table('academics')
            ->where('time_out_remark', 'On Time')
            ->update(['time_out_remark' => 'ontime']);
            
        DB::table('academics')
            ->where('time_out_remark', 'Late')
            ->update(['time_out_remark' => 'late']);
            
        DB::table('academics')
            ->where('time_out_remark', 'Early')
            ->update(['time_out_remark' => 'early']);
            
        DB::table('academics')
            ->where('time_in_remark', 'On Time')
            ->update(['time_in_remark' => 'ontime']);
            
        DB::table('academics')
            ->where('time_in_remark', 'Late')
            ->update(['time_in_remark' => 'late']);
            
        DB::table('academics')
            ->where('time_in_remark', 'Early')
            ->update(['time_in_remark' => 'early']);
    }
};
