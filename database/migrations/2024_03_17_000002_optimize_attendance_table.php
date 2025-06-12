<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Helper function to check if column exists
        $columnExists = function($table, $column) {
            return Schema::hasColumn($table, $column);
        };

        // Add computed columns if they don't exist
        Schema::table('attendances', function (Blueprint $table) use ($columnExists) {
            if (!$columnExists('attendances', 'attendance_date')) {
                $table->date('attendance_date')->nullable()
                    ->comment('Computed date from check_in timestamp for faster date-based queries');
            }
            
            if (!$columnExists('attendances', 'check_in_time')) {
                $table->time('check_in_time')->nullable()
                    ->comment('Computed time from check_in timestamp for time-based analysis');
            }
            
            if (!$columnExists('attendances', 'check_out_time')) {
                $table->time('check_out_time')->nullable()
                    ->comment('Computed time from check_out timestamp for time-based analysis');
            }
            
            if (!$columnExists('attendances', 'total_hours')) {
                $table->decimal('total_hours', 5, 2)->nullable()
                    ->comment('Pre-calculated work duration in hours');
            }
        });

        // Check if we have the timestamp columns before updating
        $hasTimestampColumns = $columnExists('attendances', 'time_in') || $columnExists('attendances', 'check_in');
        $checkInColumn = $columnExists('attendances', 'check_in') ? 'check_in' : 'time_in';
        $checkOutColumn = $columnExists('attendances', 'check_out') ? 'check_out' : 'time_out';

        if ($hasTimestampColumns) {
            // Update existing records with computed values
            DB::statement("
                UPDATE attendances 
                SET attendance_date = DATE({$checkInColumn}),
                    check_in_time = TIME({$checkInColumn}),
                    check_out_time = CASE 
                        WHEN {$checkOutColumn} IS NOT NULL THEN TIME({$checkOutColumn})
                        ELSE NULL 
                    END,
                    total_hours = CASE 
                        WHEN {$checkOutColumn} IS NOT NULL 
                        THEN ROUND(TIMESTAMPDIFF(SECOND, {$checkInColumn}, {$checkOutColumn}) / 3600.0, 2)
                        ELSE NULL 
                    END
                WHERE {$checkInColumn} IS NOT NULL
            ");
        }

        // Add indexes for the computed columns
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasIndex('attendances', 'idx_attendance_date')) {
                $table->index('attendance_date', 'idx_attendance_date');
            }
            
            if (!Schema::hasIndex('attendances', 'idx_attendance_date_employee')) {
                $table->index(['attendance_date', 'employee_id'], 'idx_attendance_date_employee');
            }
            
            if (!Schema::hasIndex('attendances', 'idx_attendance_datetime')) {
                $table->index(['attendance_date', 'check_in_time'], 'idx_attendance_datetime');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Drop indexes if they exist
            if (Schema::hasIndex('attendances', 'idx_attendance_date')) {
                $table->dropIndex('idx_attendance_date');
            }
            
            if (Schema::hasIndex('attendances', 'idx_attendance_date_employee')) {
                $table->dropIndex('idx_attendance_date_employee');
            }
            
            if (Schema::hasIndex('attendances', 'idx_attendance_datetime')) {
                $table->dropIndex('idx_attendance_datetime');
            }
            
            // Drop columns if they exist
            $columns = ['attendance_date', 'check_in_time', 'check_out_time', 'total_hours'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('attendances', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}; 