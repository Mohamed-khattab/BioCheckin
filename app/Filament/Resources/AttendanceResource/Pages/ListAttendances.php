<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use App\Filament\Widgets\AttendanceOverviewWidget;
use App\Filament\Widgets\AttendanceTrendWidget;
use App\Filament\Widgets\LatestAttendanceWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AttendanceOverviewWidget::class,
            AttendanceTrendWidget::class,
        ];
    }

}
