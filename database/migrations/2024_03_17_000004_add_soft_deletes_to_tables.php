<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Helper function to check if column exists
        $columnExists = function($table, $column) {
            return Schema::hasColumn($table, $column);
        };

        // Add soft deletes to departments table
        if (!$columnExists('departments', 'deleted_at')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to employees table
        if (!$columnExists('employees', 'deleted_at')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to devices table
        if (!$columnExists('devices', 'deleted_at')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to attendances table
        if (!$columnExists('attendances', 'deleted_at')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to leave_requests table
        if (!$columnExists('leave_requests', 'deleted_at')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        // Helper function to check if column exists
        $columnExists = function($table, $column) {
            return Schema::hasColumn($table, $column);
        };

        // Remove soft deletes from departments table
        if ($columnExists('departments', 'deleted_at')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        // Remove soft deletes from employees table
        if ($columnExists('employees', 'deleted_at')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        // Remove soft deletes from devices table
        if ($columnExists('devices', 'deleted_at')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        // Remove soft deletes from attendances table
        if ($columnExists('attendances', 'deleted_at')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        // Remove soft deletes from leave_requests table
        if ($columnExists('leave_requests', 'deleted_at')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
}; 