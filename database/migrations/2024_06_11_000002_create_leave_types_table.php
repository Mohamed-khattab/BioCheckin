<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveTypesTable extends Migration
{
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->string('code')->unique()->after('name')->nullable();
            $table->text('description')->nullable()->after('code');
            $table->softDeletes()->after('description');
        });

        // Add leave_type_id to leave_requests table
        // Schema::table('leave_requests', function (Blueprint $table) {
        //     $table->foreignId('leave_type_id')->after('employee_id')->nullable()
        //         ->constrained()->nullOnDelete();
        // });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['leave_type_id']);
            $table->dropColumn('leave_type_id');
        });
        
        Schema::dropIfExists('leave_types');
    }
} 