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

        // Helper function to check if index exists
        $indexExists = function($table, $index) {
            return collect(DB::select("SHOW INDEXES FROM {$table}"))->contains('Key_name', $index);
        };

        // Add monitoring columns if they don't exist
        Schema::table('devices', function (Blueprint $table) use ($columnExists) {
            // Add monitoring counters
            if (!$columnExists('devices', 'total_records_today')) {
                $table->integer('total_records_today')->default(0)->after('last_sync')
                    ->comment('Number of attendance records processed today');
            }
            
            if (!$columnExists('devices', 'total_records_week')) {
                $table->integer('total_records_week')->default(0)->after('total_records_today')
                    ->comment('Number of attendance records processed this week');
            }
            
            if (!$columnExists('devices', 'total_records_month')) {
                $table->integer('total_records_month')->default(0)->after('total_records_week')
                    ->comment('Number of attendance records processed this month');
            }
            
            // Add error tracking columns
            if (!$columnExists('devices', 'last_error_at')) {
                $table->timestamp('last_error_at')->nullable()->after('total_records_month')
                    ->comment('Timestamp of the last error occurrence');
            }
            
            if (!$columnExists('devices', 'last_error_message')) {
                $table->string('last_error_message', 500)->nullable()->after('last_error_at')
                    ->comment('Details of the last error');
            }
            
            if (!$columnExists('devices', 'consecutive_failures')) {
                $table->integer('consecutive_failures')->default(0)->after('last_error_message')
                    ->comment('Number of consecutive sync failures');
            }
            
            // Add performance metrics storage
            if (!$columnExists('devices', 'performance_metrics')) {
                $table->json('performance_metrics')->nullable()->after('consecutive_failures')
                    ->comment('JSON storage for various performance metrics like response time, success rate, etc.');
            }
        });

        // Add monitoring indexes if they don't exist
        Schema::table('devices', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('devices', 'idx_device_daily_records')) {
                $table->index('total_records_today', 'idx_device_daily_records');
            }
            
            if (!$indexExists('devices', 'idx_device_failures')) {
                $table->index('consecutive_failures', 'idx_device_failures');
            }
            
            if (!$indexExists('devices', 'idx_device_last_error')) {
                $table->index('last_error_at', 'idx_device_last_error');
            }
        });
    }

    public function down(): void
    {
        $indexExists = function($table, $index) {
            return collect(DB::select("SHOW INDEXES FROM {$table}"))->contains('Key_name', $index);
        };

        $columnExists = function($table, $column) {
            return Schema::hasColumn($table, $column);
        };

        Schema::table('devices', function (Blueprint $table) use ($indexExists, $columnExists) {
            // Drop indexes first if they exist
            if ($indexExists('devices', 'idx_device_daily_records')) {
                $table->dropIndex('idx_device_daily_records');
            }
            
            if ($indexExists('devices', 'idx_device_failures')) {
                $table->dropIndex('idx_device_failures');
            }
            
            if ($indexExists('devices', 'idx_device_last_error')) {
                $table->dropIndex('idx_device_last_error');
            }
            
            // Then drop columns if they exist
            $columns = [
                'total_records_today',
                'total_records_week',
                'total_records_month',
                'last_error_at',
                'last_error_message',
                'consecutive_failures',
                'performance_metrics'
            ];
            
            $existingColumns = array_filter($columns, function($column) use ($columnExists) {
                return $columnExists('devices', $column);
            });
            
            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });
    }
}; 