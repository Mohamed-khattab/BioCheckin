<?php

namespace App\Filament\Resources\LeaveRequestResource\Pages;

use App\Filament\Resources\LeaveRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\LeaveRequestResource\Widgets\LeaveRequestStatsWidget;
use App\Filament\Resources\LeaveRequestResource\Widgets\LeaveCalendarWidget;

class ListLeaveRequests extends ListRecords
{
    protected static string $resource = LeaveRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            LeaveRequestStatsWidget::class,
            LeaveCalendarWidget::class,
        ];
    }
} 