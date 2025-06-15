<?php

namespace App\Filament\Resources\WorkScheduleResource\Pages;

use App\Filament\Resources\WorkScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\WorkScheduleResource\Widgets;

class ListWorkSchedules extends ListRecords
{
    protected static string $resource = WorkScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            Widgets\ScheduleStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            Widgets\ScheduleDistributionWidget::class,
        ];
    }
} 