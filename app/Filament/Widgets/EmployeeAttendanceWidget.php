<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class EmployeeAttendanceWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        return [
            $this->getAttendanceStats($today, 'Today'),
            $this->getAttendanceStats($yesterday, 'Yesterday'),
            $this->getLateStats($today),
        ];
    }

    protected function getAttendanceStats($date, $label): Stat
    {
        $count = Attendance::whereDate('check_in', $date)
            ->distinct('employee_id')
            ->count();

        return Stat::make("$label's Attendance", $count)
            ->description("Employees present $label")
            ->descriptionIcon('heroicon-m-user-group')
            ->color('success');
    }

    protected function getLateStats($date): Stat
    {
        $count = Attendance::whereDate('check_in', $date)
            ->whereTime('check_in', '>', '09:00:00')
            ->distinct('employee_id')
            ->count();

        return Stat::make('Late Arrivals', $count)
            ->description('Employees late today')
            ->descriptionIcon('heroicon-m-clock')
            ->color($count > 0 ? 'danger' : 'success');
    }
} 