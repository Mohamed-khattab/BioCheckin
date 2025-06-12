<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DeviceActivityWidget extends ChartWidget
{
    protected static ?string $heading = 'Attendance Activity';
    
    protected static ?string $pollingInterval = '30s';
    protected static ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = '1';

    protected function getData(): array
    {
        $data = Attendance::query()
            ->select(DB::raw('HOUR(check_in) as hour'), DB::raw('COUNT(*) as count'))
            ->whereDate('check_in', Carbon::today())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $hours = range(0, 23);
        $counts = array_fill(0, 24, 0);

        foreach ($data as $record) {
            $counts[$record->hour] = $record->count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Check-ins Today',
                    'data' => $counts,
                    'backgroundColor' => '#60A5FA',
                    'borderColor' => '#2563EB',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => array_map(function ($hour) {
                return sprintf('%02d:00', $hour);
            }, $hours),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'elements' => [
                'line' => [
                    'tension' => 0.3,
                ],
                'point' => [
                    'radius' => 4,
                    'hoverRadius' => 6,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
} 