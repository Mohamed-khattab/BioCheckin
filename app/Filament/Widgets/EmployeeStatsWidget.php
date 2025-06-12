<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class EmployeeStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    // Make it full width
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('is_active', true)->count();
        
        // Get today's attendance stats
        $checkedInToday = Attendance::whereDate('check_in', Carbon::today())
            ->distinct('employee_id')
            ->count();
            
        $checkedOutToday = Attendance::whereDate('check_out', Carbon::today())
            ->whereNotNull('check_out')
            ->distinct('employee_id')
            ->count();

        return [
            Stat::make('Total Employees', $totalEmployees)
                ->description('Total registered employees')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
                
            Stat::make('Active Employees', $activeEmployees)
                ->description('Currently active employees')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
                
            Stat::make('Present Today', $checkedInToday)
                ->description('Employees checked in today')
                ->descriptionIcon('heroicon-m-finger-print')
                ->color('primary'),
                
            Stat::make('Completed Shifts', $checkedOutToday)
                ->description('Employees checked out today')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
} 