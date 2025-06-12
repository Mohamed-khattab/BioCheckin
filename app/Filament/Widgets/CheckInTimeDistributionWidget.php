<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CheckInTimeDistributionWidget extends ChartWidget
{
    protected static ?string $heading = 'Daily Check-in Time Distribution';
    protected static ?int $sort = 3;
    protected static ?string $pollingInterval = '15s';
    protected static ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = ['lg' => 1];

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'height' => 300,
            'scales' => [
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Hour of Day'
                    ]
                ],
                'y' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Number of Check-ins'
                    ],
                    'beginAtZero' => true,
                ]
            ],
        ];
    }

    protected function getData(): array
    {
        $data = Attendance::query()
            ->whereDate('check_in', Carbon::today())
            ->select(DB::raw('HOUR(check_in) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Fill in missing hours with 0
        $fullData = array_fill(0, 24, 0);
        foreach ($data as $hour => $count) {
            $fullData[$hour] = $count;
        }

        // Create hour labels in 12-hour format with AM/PM
        $labels = array_map(function($hour) {
            return Carbon::createFromTime($hour)->format('g:00 A');
        }, range(0, 23));

        return [
            'datasets' => [
                [
                    'label' => 'Check-ins',
                    'data' => array_values($fullData),
                    'backgroundColor' => '#60A5FA', // blue
                    'borderColor' => '#3B82F6',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
} 