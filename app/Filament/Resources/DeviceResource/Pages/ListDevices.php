<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Resources\DeviceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\DeviceStatusWidget;
use App\Filament\Widgets\DeviceActivityWidget;
use App\Filament\Widgets\DeviceSyncWidget;

class ListDevices extends ListRecords
{
    protected static string $resource = DeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DeviceStatusWidget::class,
            DeviceActivityWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            DeviceSyncWidget::class,
        ];
    }
} 