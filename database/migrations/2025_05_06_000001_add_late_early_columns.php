<?php
// database/migrations/2025_05_06_000001_add_late_early_columns.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add columns to hrm_settings
        Schema::table('hrm_settings', function (Blueprint $table) {
            $table->integer('late_grace_minutes')->default(15)->after('default_overtime_hour');
            $table->integer('early_grace_minutes')->default(15)->after('late_grace_minutes');
            $table->integer('working_hours_per_day')->default(8)->after('early_grace_minutes');
            $table->boolean('late_deduction_enabled')->default(true)->after('working_hours_per_day');
            $table->integer('late_hours_for_full_day_deduction')->default(8)->after('late_deduction_enabled'); // ✅ New field
        });

        // Add columns to attendance_records
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->integer('late_minutes')->default(0)->after('check_out');
            $table->integer('early_minutes')->default(0)->after('late_minutes');
            $table->integer('total_late_early_minutes')->default(0)->after('early_minutes');
        });
    }

    public function down()
    {
        Schema::table('hrm_settings', function (Blueprint $table) {
            $table->dropColumn('late_grace_minutes');
            $table->dropColumn('early_grace_minutes');
            $table->dropColumn('working_hours_per_day');
            $table->dropColumn('late_deduction_enabled');
            $table->dropColumn('late_hours_for_full_day_deduction');
        });

        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropColumn('late_minutes');
            $table->dropColumn('early_minutes');
            $table->dropColumn('total_late_early_minutes');
        });
    }
};
