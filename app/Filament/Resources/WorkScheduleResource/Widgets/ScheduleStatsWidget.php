<?php

namespace App\Filament\Resources\WorkScheduleResource\Widgets;

use App\Models\WorkSchedule;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class ScheduleStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $schedules = WorkSchedule::query();
        $activeSchedules = $schedules->where('is_active', true);
        
        $employeesWithOneRestDay = WorkSchedule::where('is_active', true)
            ->whereJsonLength('rest_days', 1)
            ->count();
            
        $employeesWithTwoRestDays = WorkSchedule::where('is_active', true)
            ->whereJsonLength('rest_days', 2)
            ->count();

        $averageWorkingHours = WorkSchedule::where('is_active', true)
            ->avg('working_hours_per_day');

        return [
            Stat::make('Active Schedules', $activeSchedules->count())
                ->description('Currently active work schedules')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success'),

            Stat::make('1 Rest Day', $employeesWithOneRestDay)
                ->description('Employees with 1 rest day')
                ->descriptionIcon('heroicon-m-user')
                ->color('info'),

            Stat::make('2 Rest Days', $employeesWithTwoRestDays)
                ->description('Employees with 2 rest days')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),

            Stat::make('Average Working Hours', number_format($averageWorkingHours, 1))
                ->description('Hours per day across all schedules')
                ->descriptionIcon('heroicon-m-clock')
                ->color('gray'),
        ];
    }
} 