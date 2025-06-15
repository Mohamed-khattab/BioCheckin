<?php

namespace App\Filament\Widgets;

use App\Models\FingerprintDevice;
use App\Models\Attendance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class FingerprintDeviceActivityWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $devices = FingerprintDevice::query();
        
        // Get online devices (devices with activity in last 5 minutes)
        $onlineDevices = $devices->where('last_sync', '>=', now()->subMinutes(5))->count();
        
        // Get today's check-ins count
        $todayCheckIns = Attendance::whereDate('check_in_time', Carbon::today())->count();

        // Get total registered devices
        $totalDevices = $devices->count();

        return [
            Stat::make('Total Devices', $totalDevices)
            ->description('Total registered devices')
            ->descriptionIcon('heroicon-m-device-phone-mobile')
            ->color('gray'),

            Stat::make('Online Devices', $onlineDevices)
                ->description('Devices active in last 5 minutes')
                ->descriptionIcon('heroicon-m-signal')
                ->color('success'),

            Stat::make("Today's Check-ins", $todayCheckIns)
                ->description('Total check-ins recorded today')
                ->descriptionIcon('heroicon-m-finger-print')
                ->color('info'),
           
        ];
    }
} 