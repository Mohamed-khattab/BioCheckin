<?php

namespace App\Filament\Resources\LeaveRequestResource\Widgets;

use App\Models\LeaveRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class LeaveRequestStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $thisMonth = Carbon::now()->month;
        
        return [
            Stat::make('Pending Requests', LeaveRequest::where('status', 'pending')->count())
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
                
            Stat::make('Approved This Month', 
                LeaveRequest::where('status', 'approved')
                    ->whereMonth('created_at', $thisMonth)
                    ->count()
            )
                ->description('Requests approved this month')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('Total Days Approved',
                LeaveRequest::where('status', 'approved')
                    ->whereMonth('created_at', $thisMonth)
                    ->sum('total_days')
            )
                ->description('Days approved this month')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
        ];
    }
} 