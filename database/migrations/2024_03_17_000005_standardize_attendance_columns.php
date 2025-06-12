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

        Schema::table('attendances', function (Blueprint $table) use ($columnExists) {
            // First, add all the new columns we need
            if (!$columnExists('attendances', 'check_in')) {
                $table->timestamp('check_in')->nullable();
            }
            
            if (!$columnExists('attendances', 'check_out')) {
                $table->timestamp('check_out')->nullable();
            }
            
            if (!$columnExists('attendances', 'status')) {
                $table->string('status')->default('present');
            }
            
            if (!$columnExists('attendances', 'notes')) {
                $table->text('notes')->nullable();
            }
            
            if (!$columnExists('attendances', 'device_id')) {
                $table->foreignId('device_id')->nullable()->constrained()->onDelete('set null');
            }
        });

        // Now that new columns exist, migrate the data
        if ($columnExists('attendances', 'timestamp')) {
            // Copy check-in data
            DB::statement('UPDATE attendances SET check_in = timestamp');
            
            // Copy check-out data for records with state = 0
            DB::statement('UPDATE attendances SET check_out = timestamp WHERE state = 0');
            
            // Drop old columns in a separate schema operation
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropColumn(['timestamp', 'state', 'type']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // We don't want to risk data loss in the down migration
            // So we'll just add back the old columns if they don't exist
            if (!Schema::hasColumn('attendances', 'timestamp')) {
                $table->timestamp('timestamp')->nullable();
                $table->integer('state')->nullable();
                $table->integer('type')->nullable();
            }
        });
    }
}; 