<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class DeviceStatusWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $devices = Device::all();
        $totalDevices = $devices->count();
        $onlineDevices = $devices->where('is_online', true)->count();
        $lastSync = $devices->max('last_sync');

        return [
            Stat::make('Total Devices', $totalDevices)
                ->description('Total registered devices')
                ->descriptionIcon('heroicon-m-computer-desktop')
                ->color('gray'),

            Stat::make('Online Devices', $onlineDevices)
                ->description($onlineDevices . ' of ' . $totalDevices . ' devices online')
                ->descriptionIcon('heroicon-m-signal')
                ->color($onlineDevices === $totalDevices ? 'success' : ($onlineDevices > 0 ? 'warning' : 'danger')),

            Stat::make('Last Sync', $lastSync ? Carbon::parse($lastSync)->diffForHumans() : 'Never')
                ->description('Most recent device synchronization')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color($lastSync && Carbon::parse($lastSync)->gt(now()->subMinutes(5)) ? 'success' : 'warning'),
        ];
    }
} 