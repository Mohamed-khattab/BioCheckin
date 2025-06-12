<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Device;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Employees', Employee::count())
                ->description('Total registered employees')
                ->descriptionIcon('heroicon-m-users')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

            Stat::make('Present Today', 
                Attendance::whereDate('check_in', Carbon::today())->count())
                ->description('Employees checked in today')
                ->descriptionIcon('heroicon-m-finger-print')
                ->color('success'),

            Stat::make('Active Devices', Device::where('is_online', true)->count())
                ->description(Device::where('is_online', true)->count() . ' / ' . Device::count() . ' Connected biometric devices')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('success'),
        ];
    }
} 