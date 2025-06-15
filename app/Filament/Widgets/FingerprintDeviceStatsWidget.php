<?php

namespace App\Filament\Widgets;

use App\Models\FingerprintDevice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class FingerprintDeviceStatsWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $devices = FingerprintDevice::query();
        $totalDevices = $devices->count();
        $onlineDevices = $devices->where('is_online', true)->count();
        $syncedToday = FingerprintDevice::where('last_sync', '>=', Carbon::today())->count();

        return [
            Stat::make('Total Devices', $totalDevices)
                ->description('Total registered devices')
                ->descriptionIcon('heroicon-m-computer-desktop')
                ->color('gray'),

            Stat::make('Online Devices', $onlineDevices)
                ->description($onlineDevices . ' of ' . $totalDevices . ' devices online')
                ->descriptionIcon('heroicon-m-signal')
                ->color($onlineDevices === $totalDevices ? 'success' : ($onlineDevices > 0 ? 'warning' : 'danger')),

            Stat::make('Synced Today', $syncedToday)
                ->description($syncedToday . ' of ' . $totalDevices . ' devices synced')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color($syncedToday === $totalDevices ? 'success' : ($syncedToday > 0 ? 'warning' : 'danger')),

            Stat::make('Devices with Issues', 
                FingerprintDevice::where('consecutive_failures', '>', 0)->count())
                ->description('Devices needing attention')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('danger'),
            
        ];
    }
} 