<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceTrendWidget extends ChartWidget
{
    protected static ?string $heading = 'Weekly Attendance Trend';
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = '15s';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::now()->subDays($daysAgo);
            
            $checkIns = Attendance::whereDate('check_in', $date)
                ->whereNotNull('check_in')
                ->distinct('employee_id')
                ->count();

            $checkOuts = Attendance::whereDate('check_out', $date)
                ->whereNotNull('check_out')
                ->distinct('employee_id')
                ->count();

            return [
                'date' => $date->format('D'),
                'check_ins' => $checkIns,
                'check_outs' => $checkOuts,
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Check-ins',
                    'data' => $days->pluck('check_ins')->toArray(),
                    'borderColor' => '#10B981',
                    'backgroundColor' => '#10B981',
                    'fill' => false,
                ],
                [
                    'label' => 'Check-outs',
                    'data' => $days->pluck('check_outs')->toArray(),
                    'borderColor' => '#EF4444',
                    'backgroundColor' => '#EF4444',
                    'fill' => false,
                ],
            ],
            'labels' => $days->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
} 