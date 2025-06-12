<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create departments table if it doesn't exist
        if (!Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // Create employees table if it doesn't exist
        if (!Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->id();
                $table->string('employee_id')->unique();
                $table->string('name');
                $table->string('email')->unique();
                $table->foreignId('department_id')->constrained()->onDelete('restrict');
                $table->string('position');
                $table->string('status')->default('active');
                $table->date('join_date');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Create devices table if it doesn't exist
        if (!Schema::hasTable('devices')) {
            Schema::create('devices', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('serial_number')->unique();
                $table->string('ip_address')->nullable();
                $table->boolean('is_online')->default(false);
                $table->timestamp('last_sync')->nullable();
                $table->string('firmware_version')->nullable();
                $table->string('location')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Create attendances table if it doesn't exist
        if (!Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained()->onDelete('cascade');
                $table->foreignId('device_id')->nullable()->constrained()->onDelete('set null');
                $table->timestamp('check_in');
                $table->timestamp('check_out')->nullable();
                $table->string('status')->default('present');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Create leave_requests table if it doesn't exist
        if (!Schema::hasTable('leave_requests')) {
            Schema::create('leave_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained()->onDelete('cascade');
                $table->date('start_date');
                $table->date('end_date');
                $table->string('type');
                $table->string('status')->default('pending');
                $table->text('reason')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('employees')->onDelete('set null');
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        // Only drop tables that exist
        if (Schema::hasTable('leave_requests')) {
            Schema::dropIfExists('leave_requests');
        }
        if (Schema::hasTable('attendances')) {
            Schema::dropIfExists('attendances');
        }
        if (Schema::hasTable('devices')) {
            Schema::dropIfExists('devices');
        }
        if (Schema::hasTable('employees')) {
            Schema::dropIfExists('employees');
        }
        if (Schema::hasTable('departments')) {
            Schema::dropIfExists('departments');
        }
    }
}; 