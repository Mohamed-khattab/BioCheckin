<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('days_allowed')->default(0);
            $table->boolean('requires_approval')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->foreignId('leave_type_id')->constrained('leave_types');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days');
            $table->text('reason')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('employee_id')->references('employee_id')->on('employees');
        });

        // Insert default leave types
        DB::table('leave_types')->insert([
            [
                'name' => 'Annual Leave',
                'days_allowed' => 14,
                'requires_approval' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sick Leave',
                'days_allowed' => 7,
                'requires_approval' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Emergency Leave',
                'days_allowed' => 3,
                'requires_approval' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_types');
    }
}; 