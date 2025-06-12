<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusColumnsToFingerprintDevices extends Migration
{
    public function up(): void
    {
        Schema::table('fingerprint_devices', function (Blueprint $table) {
            $table->boolean('is_online')->default(false);
            $table->timestamp('last_sync')->nullable()->after('is_online');
            $table->integer('consecutive_failures')->default(0)->after('last_sync');
            $table->string('last_error_message')->nullable()->after('consecutive_failures');
            $table->timestamp('last_error_at')->nullable()->after('last_error_message');
        });
    }

    public function down(): void
    {
        Schema::table('fingerprint_devices', function (Blueprint $table) {
            $table->dropColumn([
                'is_online',
                'last_sync',
                'consecutive_failures',
                'last_error_message',
                'last_error_at'
            ]);
        });
    }
} 