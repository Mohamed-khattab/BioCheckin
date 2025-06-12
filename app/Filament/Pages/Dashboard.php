<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\AttendanceTrendWidget;
use App\Filament\Widgets\DepartmentDistributionWidget;
use App\Filament\Widgets\DeviceStatusWidget;
use App\Filament\Widgets\CheckInTimeDistributionWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
            DeviceStatusWidget::class,
        ];
    }

    public function getWidgets(): array
    {
        return [
            AttendanceTrendWidget::class,
            DepartmentDistributionWidget::class,
            CheckInTimeDistributionWidget::class,
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}