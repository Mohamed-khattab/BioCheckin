<?php

namespace App\Filament\Resources\FingerprintDeviceResource\Pages;

use App\Filament\Resources\FingerprintDeviceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\FingerprintDeviceActivityWidget;
use App\Filament\Widgets\FingerprintDeviceActivityChartWidget;

class ListFingerprintDevices extends ListRecords
{
    protected static string $resource = FingerprintDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FingerprintDeviceActivityWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            FingerprintDeviceActivityChartWidget::class,
        ];
    }
}
