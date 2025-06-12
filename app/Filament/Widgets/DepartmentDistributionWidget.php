<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DepartmentDistributionWidget extends ChartWidget
{
    protected static ?int $sort = 2;
    
    protected static ?string $heading = 'Employees by Department';

    protected static ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = ['lg' => 1];

    protected function getData(): array
    {
        $data = Employee::query()
            ->select(DB::raw('COALESCE(department, "Unassigned") as department'), DB::raw('count(*) as count'))
            ->groupBy('department')
            ->get();
        return [
            'datasets' => [
                [
                    'label' => 'Employees',
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => [
                        '#10B981', // green
                        '#3B82F6', // blue
                        '#6366F1', // indigo
                        '#8B5CF6', // purple
                        '#EC4899', // pink
                        '#F59E0B', // yellow
                        '#6B7280', // gray
                    ],
                ],
            ],
            'labels' => $data->pluck('department')->toArray(),
            'options' => [
                'plugins' => [
                    'tooltip' => [
                        'callbacks' => [
                            'label' => "function(context) {
                                return context.label + ': ' + context.raw + ' employees';
                            }"
                        ]
                    ]
                ]
            ]
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
} 