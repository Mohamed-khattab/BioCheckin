<?php

namespace App\Filament\Resources\LeaveRequestResource\Widgets;

use App\Models\LeaveRequest;
use Filament\Widgets\Widget;
use Carbon\Carbon;

class LeaveCalendarWidget extends Widget
{
    protected static string $view = 'filament.resources.leave-request-resource.widgets.leave-calendar-widget';
    
    protected int | string | array $columnSpan = 2;

    public function getViewData(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $leaves = LeaveRequest::with(['employee', 'leaveType'])
            ->whereBetween('start_date', [$startOfMonth, $endOfMonth])
            ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
            ->get()
            ->map(function ($leave) {
                $employeeName = $leave->employee?->name ?? 'Unknown Employee';
                $leaveTypeName = $leave->leaveType?->name ?? 'Unknown Leave Type';
                
                return [
                    'title' => $employeeName . ' - ' . $leaveTypeName,
                    'start' => $leave->start_date->format('Y-m-d'),
                    'end' => $leave->end_date->addDay()->format('Y-m-d'),
                    'color' => match($leave->status) {
                        'approved' => '#10B981',
                        'rejected' => '#EF4444',
                        default => '#F59E0B',
                    },
                ];
            });

        return [
            'leaves' => $leaves,
        ];
    }
} 