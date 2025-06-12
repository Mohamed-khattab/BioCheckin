<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\FingerprintDevice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AttendanceOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '30s';
    protected static ?string $maxHeight = '400px';
    protected int | string | array $columnSpan = ['lg' => 2];

    protected function getStats(): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Today's attendance count
        $todayCount = Attendance::whereDate('check_in', $today)
            ->distinct('employee_id')
            ->count();

        // Yesterday's attendance count
        $yesterdayCount = Attendance::whereDate('check_in', $yesterday)
            ->distinct('employee_id')
            ->count();

        // Late arrivals today
        $lateCount = Attendance::whereDate('check_in', $today)
            ->whereTime('check_in', '>', '09:00:00')
            ->distinct('employee_id')
            ->count();

        return [
            Stat::make('Active Employees', Employee::count())
            ->description('Total registered employees')
            ->descriptionIcon('heroicon-m-user-group')
            ->color('info')
            ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

            Stat::make('Today\'s Attendance', $todayCount)
                ->description('Employees present today')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

            Stat::make('Yesterday\'s Absences', Employee::count() - $yesterdayCount)
                ->description('Employees absent yesterday') 
                ->descriptionIcon('heroicon-m-calendar')
                ->color('danger')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

            Stat::make('Late Arrivals', $lateCount)
                ->description('Employees late today')
                ->descriptionIcon('heroicon-m-clock')
                ->color($lateCount > 0 ? 'danger' : 'success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

        ];
    }
} 