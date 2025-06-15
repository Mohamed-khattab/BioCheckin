<?php

namespace App\Filament\Resources\WorkScheduleResource\Widgets;

use App\Models\WorkSchedule;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class ScheduleDistributionWidget extends ChartWidget
{
    protected static ?string $heading = 'Rest Day Distribution';

    protected static ?string $pollingInterval = '15s';

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $days = [
            'Sunday', 'Monday', 'Tuesday', 'Wednesday',
            'Thursday', 'Friday', 'Saturday'
        ];

        $restDayCounts = collect($days)->map(function ($day, $index) {
            return WorkSchedule::where('is_active', true)
                ->whereJsonContains('rest_days', $index)
                ->count();
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Employees with Rest Day',
                    'data' => $restDayCounts,
                    'backgroundColor' => [
                        '#f87171', '#fb923c', '#fbbf24', '#a3e635',
                        '#34d399', '#22d3ee', '#818cf8'
                    ],
                ],
            ],
            'labels' => $days,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}