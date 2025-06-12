<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Helper function to check if index exists
        $indexExists = function($table, $index) {
            return collect(DB::select("SHOW INDEXES FROM {$table}"))->contains('Key_name', $index);
        };

        // Helper function to check if columns exist
        $columnsExist = function($table, $columns) {
            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    return false;
                }
            }
            return true;
        };

        // Add composite index for employee attendance queries
        if ($columnsExist('attendances', ['employee_id', 'check_in']) && 
            !$indexExists('attendances', 'idx_attendance_employee_checkin')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->index(['employee_id', 'check_in'], 'idx_attendance_employee_checkin');
            });
        }

        // Add composite index for device status queries
        if ($columnsExist('devices', ['is_online', 'last_sync']) && 
            !$indexExists('devices', 'idx_device_status_sync')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->index(['is_online', 'last_sync'], 'idx_device_status_sync');
            });
        }

        // Add composite index for department-wise employee status
        if ($columnsExist('employees', ['department_id', 'status']) && 
            !$indexExists('employees', 'idx_employee_dept_status')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->index(['department_id', 'status'], 'idx_employee_dept_status');
            });
        }

        // Add composite indexes for leave request queries
        if ($columnsExist('leave_requests', ['employee_id', 'status']) && 
            !$indexExists('leave_requests', 'idx_leave_employee_status')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->index(['employee_id', 'status'], 'idx_leave_employee_status');
            });
        }
        
        if ($columnsExist('leave_requests', ['start_date', 'end_date']) && 
            !$indexExists('leave_requests', 'idx_leave_date_range')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->index(['start_date', 'end_date'], 'idx_leave_date_range');
            });
        }
    }

    public function down(): void
    {
        $indexExists = function($table, $index) {
            return collect(DB::select("SHOW INDEXES FROM {$table}"))->contains('Key_name', $index);
        };

        if ($indexExists('attendances', 'idx_attendance_employee_checkin')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropIndex('idx_attendance_employee_checkin');
            });
        }

        if ($indexExists('devices', 'idx_device_status_sync')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->dropIndex('idx_device_status_sync');
            });
        }

        if ($indexExists('employees', 'idx_employee_dept_status')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropIndex('idx_employee_dept_status');
            });
        }

        if ($indexExists('leave_requests', 'idx_leave_employee_status')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->dropIndex('idx_leave_employee_status');
            });
        }

        if ($indexExists('leave_requests', 'idx_leave_date_range')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->dropIndex('idx_leave_date_range');
            });
        }
    }
}; 