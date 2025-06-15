<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->decimal('working_hours', 5, 2)->nullable()->after('total_hours');
            $table->boolean('is_late')->default(false)->after('working_hours');
            $table->boolean('is_early_departure')->default(false)->after('is_late');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['working_hours', 'is_late', 'is_early_departure']);
        });
    }
}; 