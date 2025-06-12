<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FingerprintDeviceActivityWidget extends ChartWidget
{
    protected static ?string $heading = 'Device Activity Today';
    protected static ?string $pollingInterval = '30s';
    protected static ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = 2;

    protected function getData(): array
    {
        $data = Attendance::query()
            ->whereDate('check_in', Carbon::today())
            ->whereNotNull('device_id') // Only get attendance records from devices
            ->select(DB::raw('HOUR(check_in) as hour'), DB::raw('COUNT(*) as count'))
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
                    'label' => 'Device Scans',
                    'data' => $counts,
                    'backgroundColor' => '#60A5FA',
                    'borderColor' => '#2563EB',
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
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) {
                            return context.parsed.y + ' scans';
                        }"
                    ]
                ]
            ],
        ];
    }
} 